<?php

namespace Axtiva\FlexibleGraphql\Tests\Generator\ResolverProvider;

use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\CodeGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\FieldResolverGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\ResolverProvider\Foundation\ContainerCallFieldResolverProvider;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use PHPUnit\Framework\TestCase;

class ContainerCallFieldResolverProviderTest extends TestCase
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
        $dir = '/tmp/TmpTestData/GraphQL';

        $mainConfig = new CodeGeneratorConfig($dir, $languageLevel, $namespace);
        $fieldConfig = new FieldResolverGeneratorConfig($mainConfig);
        $generator = new ContainerCallFieldResolverProvider();

        /** @var ObjectType $type */
        $type = $schema->getType($typeName);
        $this->assertInstanceOf(ObjectType::class, $type);

        $this->assertNotFalse($type->hasField($fieldName));
        $field = $type->getField($fieldName);

        $this->assertEquals($expected, $generator->generate($fieldConfig, $type, $field));
    }

    public function dataProviderGeneratePhpCode(): iterable
    {
        yield [
            'NamedCurrency',
            'id',
            CodeGeneratorConfig::V7_4,
            BuildSchema::build(Parser::parse(<<<GQL
type NamedCurrency {
    id: ID!
}
GQL)),
            <<<'PHP'
$this->container->get('Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\NamedCurrency\IdResolver')
PHP,
        ];
    }
}