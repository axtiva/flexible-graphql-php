<?php

namespace Axtiva\FlexibleGraphql\Tests\Generator\Model\Psr4;

use Axtiva\FlexibleGraphql\Builder\Foundation\CodeGeneratorBuilder;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\CodeGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\FederationRepresentationResolverGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\FieldResolverGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Model\Foundation\Psr4\_EntitiesResolverGenerator;
use Axtiva\FlexibleGraphql\Generator\Model\Foundation\Psr4\_ServiceResolverGenerator;
use Axtiva\FlexibleGraphql\Generator\Model\Foundation\Psr4\FederationRepresentationResolverGenerator;
use Axtiva\FlexibleGraphql\Tests\Helper\FixtureLoader;
use Axtiva\FlexibleGraphql\Tests\Helper\FileSystemHelper;
use Axtiva\FlexibleGraphql\Utils\FederationV1SchemaExtender;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FederationRepresentationResolverGeneratorTest extends TestCase
{
    /**
     * @return void
     * @dataProvider dataProviderGeneratePhpCode
     */
#[DataProvider('dataProviderGeneratePhpCode')]
    public function testGeneratePhpCode(
        string $typeName,
        string $languageLevel,
        Schema $schema,
        string $expectedFixturePath
    ) {
        $namespace = 'Axtiva\FlexibleGraphql\Example\GraphQL';
        $dir = '/tmp/TmpTestData/GraphQL';

        FileSystemHelper::rmdir($dir);
        FileSystemHelper::mkdir($dir);

        $mainConfig = new CodeGeneratorConfig($dir, $languageLevel, $namespace);
        $builder = new CodeGeneratorBuilder($mainConfig);
        $fieldResolverConfig = new FieldResolverGeneratorConfig($mainConfig);
        $representationConfig = new FederationRepresentationResolverGeneratorConfig($mainConfig);

        $builder->addFieldResolverGenerator(new _EntitiesResolverGenerator($fieldResolverConfig));
        $builder->addFieldResolverGenerator(new _ServiceResolverGenerator($fieldResolverConfig));
        $builder->addModelGenerator(new FederationRepresentationResolverGenerator($representationConfig));
        $generator = $builder->build();

        /** @var ObjectType $type */
        $type = $schema->getType($typeName);
        $this->assertInstanceOf(ObjectType::class, $type);

        foreach ($generator->generateType($type, $schema) as $item) {
            $code = $item;
            if (strpos('\Representation\\', $code->getClassname())) {
                break;
            }
        }

        $this->assertTrue(isset($code));
        $generated = file_get_contents($code->getFilename());
        $this->assertNotFalse($generated);
        $expected = FixtureLoader::load($expectedFixturePath);
        $this->assertSame($expected, FixtureLoader::normalizeLineEndings($generated));

        FileSystemHelper::rmdir($dir);
    }


    /**
     * @return iterable<int, array<int, mixed>>
     */
    public static function dataProviderGeneratePhpCode(): iterable
    {
        $ast = Parser::parse(<<<GQL
# federation directives
scalar _FieldSet
directive @external on OBJECT | FIELD_DEFINITION
directive @requires(fields: _FieldSet!) on FIELD_DEFINITION
directive @provides(fields: _FieldSet!) on FIELD_DEFINITION
directive @key(fields: _FieldSet!) on OBJECT | INTERFACE
directive @extends on OBJECT | INTERFACE

type NamedCurrency @key(fields: "id") {
    id: ID!
}
GQL
        );
        yield [
            'NamedCurrency',
            CodeGeneratorConfig::V8_3,
            FederationV1SchemaExtender::build(BuildSchema::build($ast), $ast),
            __DIR__ . '/fixtures/FederationRepresentationResolverGeneratorTest/case-1.php.txt'
            ,
        ];
    }
}
