<?php

namespace Axtiva\FlexibleGraphql\Tests\Generator\ResolverProvider;

use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\ArgsFieldResolverGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\CodeGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\FieldResolverGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\ResolverProvider\Foundation\ContainerCallFieldResolverProvider;
use Axtiva\FlexibleGraphql\Generator\ResolverProvider\Foundation\WrappedContainerCallFieldResolverProvider;
use Axtiva\FlexibleGraphql\Tests\Helper\FileSystemHelper;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use PHPUnit\Framework\TestCase;

class WrappedContainerCallFieldResolverProviderTest extends TestCase
{
    /**
     * @return void
     * @dataProvider dataProviderGeneratePhpCode
     */
    public function testGeneratePhpCode(
        string $typeName,
        string $fieldName,
        string $languageLevel,
        Schema $schema,
        string $expected
    ) {
        $namespace = 'Axtiva\FlexibleGraphql\Example\GraphQL';
        $dir = uniqid('/tmp/TmpTestData/GraphQL');

        FileSystemHelper::rmdir($dir);
        FileSystemHelper::mkdir($dir);

        $mainConfig = new CodeGeneratorConfig($dir, $languageLevel, $namespace);
        $fieldConfig = new FieldResolverGeneratorConfig($mainConfig);
        $argsFieldConfig = new ArgsFieldResolverGeneratorConfig($mainConfig);
        $fieldGenerator = new ContainerCallFieldResolverProvider();

        $generator = new WrappedContainerCallFieldResolverProvider(
            $fieldGenerator,
            $argsFieldConfig
        );

        /** @var ObjectType $type */
        $type = $schema->getType($typeName);
        $this->assertInstanceOf(ObjectType::class, $type);

        $this->assertNotFalse($type->hasField($fieldName));
        $field = $type->getField($fieldName);
        $this->assertEquals($expected, $generator->generate($fieldConfig, $type, $field));

        FileSystemHelper::rmdir($dir);
    }

    public function dataProviderGeneratePhpCode(): iterable
    {
        yield [
            'NamedCurrency',
            'demo',
            CodeGeneratorConfig::V7_4,
            BuildSchema::build(Parser::parse(<<<GQL
type NamedCurrency {
    id: ID!
    demo: String
}
GQL)),
            <<<'PHP'
(function ($rootValue, $args, $context, $info) {
    
    return $this->container->get('Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\NamedCurrency\DemoResolver')($rootValue, $args, $context, $info);
})
PHP,
        ];

        require_once __DIR__ . '/resources/NameResolverArgs.php';
        yield [
            'NamedCurrency',
            'name',
            CodeGeneratorConfig::V7_4,
            BuildSchema::build(Parser::parse(<<<GQL
type NamedCurrency {
    id: ID!
    name(x: Int, testInput: DemoInput!, demo: DemoEnum, date: DateTime, hello: HelloScalar): String
}
enum DemoEnum {
    A
    B
}
input DemoInput {
  field: Int
}
scalar DateTime
scalar HelloScalar
GQL)),
            <<<'PHP'
(function ($rootValue, $args, $context, $info) {
    $args = new \Axtiva\FlexibleGraphql\Example\GraphQL\ResolverArgs\NamedCurrency\NameResolverArgs($args);
    return $this->container->get('Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\NamedCurrency\NameResolver')($rootValue, $args, $context, $info);
})
PHP,
        ];
    }
}