<?php

namespace Axtiva\FlexibleGraphql\Tests\Builder\Psr\Container;

use Axtiva\FlexibleGraphql\Builder\Foundation\Psr\Container\TypeRegistryGeneratorBuilder;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\CodeGeneratorConfig;
use Axtiva\FlexibleGraphql\Tests\Helper\FixtureLoader;
use Axtiva\FlexibleGraphql\Tests\Helper\FileSystemHelper;
use GraphQL\Language\Parser;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TypeRegistryGeneratorBuilderTest extends TestCase
{
    /**
     * @return void
     * @dataProvider dataProviderGeneratePhpCode
     */
#[DataProvider('dataProviderGeneratePhpCode')]
    public function testGeneratePhpCode(
        string $languageLevel,
        Schema $schema,
        string $expectedFixturePath
    ) {
        $namespace = 'Axtiva\FlexibleGraphql\Example\GraphQL';
        $dir = uniqid('/tmp/TmpTestData/GraphQL');

        FileSystemHelper::rmdir($dir);
        FileSystemHelper::mkdir($dir);

        $mainConfig = new CodeGeneratorConfig($dir, $languageLevel, $namespace);

        $builder = new TypeRegistryGeneratorBuilder($mainConfig);
        $generator = $builder->build();

        $generated = $generator->generate($schema);
        $expected = FixtureLoader::load($expectedFixturePath);
        $this->assertSame($expected, FixtureLoader::normalizeLineEndings($generated));

        FileSystemHelper::rmdir($dir);
    }

    /**
     * @return iterable<int, array<int, mixed>>
     */
    public static function dataProviderGeneratePhpCode(): iterable
    {
        require_once __DIR__ . '/../../../Generator/ResolverProvider/resources/NameResolverArgs.php';
        require_once __DIR__ . '/../../../Generator/TypeRegistry/resources/NameResolver.php';
        require_once __DIR__ . '/../../../Generator/TypeRegistry/resources/SumDirective.php';
        require_once __DIR__ . '/../../../Generator/TypeRegistry/resources/SumDirectiveArgs.php';
        yield [
            CodeGeneratorConfig::V8_3,
            BuildSchema::build(Parser::parse(<<<GQL
directive @sum(x: Int) on FIELD | FIELD_DEFINITION
directive @uppercase on FIELD | FIELD_DEFINITION
type NamedCurrency {
    id: ID!
    name(
    "Description for argument"
    x: Int = 5, testInput: DemoInput!, demo: DemoEnum, date: DateTime, hello: HelloScalar): String @uppercase @sum(x: 2)
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
GQL
            )),
            __DIR__ . '/../../fixtures/Psr/Container/TypeRegistryGeneratorBuilderTest/case-1.php.txt'
            ,];

        yield [
            CodeGeneratorConfig::V8_3,
            BuildSchema::build(Parser::parse(<<<GQL
type Query {
    sum: Int
}
GQL
            )),
            __DIR__ . '/../../fixtures/Psr/Container/TypeRegistryGeneratorBuilderTest/case-2.php.txt'
            ,];

        yield [
            CodeGeneratorConfig::V8_3,
            BuildSchema::build(Parser::parse(<<<GQL
type Mutation {
    sum: Int
}
GQL
            )),__DIR__ . '/../../fixtures/Psr/Container/TypeRegistryGeneratorBuilderTest/case-3.php.txt'
            ,];
    }
}
