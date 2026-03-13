<?php

namespace Axtiva\FlexibleGraphql\Tests\Generator\ResolverProvider;

use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\ArgsFieldResolverGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\CodeGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\FieldResolverGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\ResolverProvider\Foundation\ContainerCallFieldResolverProvider;
use Axtiva\FlexibleGraphql\Generator\ResolverProvider\Foundation\WrappedContainerCallFieldResolverProvider;
use Axtiva\FlexibleGraphql\Tests\Helper\FixtureLoader;
use Axtiva\FlexibleGraphql\Tests\Helper\FileSystemHelper;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class WrappedContainerCallFieldResolverProviderTest extends TestCase
{
    /**
     * @return void
     * @dataProvider dataProviderGeneratePhpCode
     */
#[DataProvider('dataProviderGeneratePhpCode')]
    public function testGeneratePhpCode(
        string $typeName,
        string $fieldName,
        string $languageLevel,
        Schema $schema,
        string $expectedFixturePath
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
        $generated = $generator->generate($fieldConfig, $type, $field);
        $expected = FixtureLoader::load($expectedFixturePath);
        $this->assertSame($expected, FixtureLoader::normalizeLineEndings($generated));

        FileSystemHelper::rmdir($dir);
    }

    /**
     * @return iterable<int, array<int, mixed>>
     */
    public static function dataProviderGeneratePhpCode(): iterable
    {
        yield [
            'NamedCurrency',
            'demo',
            CodeGeneratorConfig::V8_3,
            BuildSchema::build(Parser::parse(<<<GQL
type NamedCurrency {
    id: ID!
    demo: String
}
GQL)),
            __DIR__ . '/fixtures/WrappedContainerCallFieldResolverProviderTest/case-1.php.txt',
        ];

        require_once __DIR__ . '/resources/NameResolverArgs.php';
        yield [
            'NamedCurrency',
            'name',
            CodeGeneratorConfig::V8_3,
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
            __DIR__ . '/fixtures/WrappedContainerCallFieldResolverProviderTest/case-2.php.txt',
        ];
    }
}
