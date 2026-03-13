<?php

namespace Axtiva\FlexibleGraphql\Tests\Builder;

use Axtiva\FlexibleGraphql\Builder\Foundation\CodeGeneratorBuilder;
use Axtiva\FlexibleGraphql\Builder\Foundation\Psr\Container\TypeRegistryGeneratorBuilder;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\CodeGeneratorConfig;
use Axtiva\FlexibleGraphql\Tests\Helper\FixtureLoader;
use Axtiva\FlexibleGraphql\Tests\Helper\FileSystemHelper;
use GraphQL\Language\Parser;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CodeGeneratorBuilderTest extends TestCase
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

        $myAutoloader = function ($class) use ($namespace, $dir) {
            $prefix = $namespace;

            $baseDir = $dir;

            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                return;
            }

            $relativeClass = substr($class, $len);

            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

            if (file_exists($file)) {
                require $file;
            }
        };

        spl_autoload_register($myAutoloader);

        FileSystemHelper::rmdir($dir);
        FileSystemHelper::mkdir($dir);
        FileSystemHelper::mkdir($dir . '/ResolverArgs/NamedCurrency/');
        FileSystemHelper::mkdir($dir . '/Resolver/NamedCurrency/');
        FileSystemHelper::mkdir($dir . '/Directive/');
        FileSystemHelper::mkdir($dir . '/DirectiveArgs/');
        FileSystemHelper::mkdir($dir . '/Model/');

        copy(__DIR__ . '/../Generator/ResolverProvider/resources/NameResolverArgs.php', $dir . '/ResolverArgs/NamedCurrency/NameResolverArgs.php');
        copy(__DIR__ . '/../Generator/TypeRegistry/resources/NameResolver.php', $dir . '/Resolver/NamedCurrency/NameResolver.php');
        copy(__DIR__ . '/../Generator/TypeRegistry/resources/SumDirective.php', $dir . '/Directive/SumDirective.php');
        copy(__DIR__ . '/../Generator/TypeRegistry/resources/SumDirectiveArgs.php', $dir . '/DirectiveArgs/SumDirectiveArgs.php');
        copy(__DIR__ . '/../Generator/Model/Psr4/resources/AltCoinType.php', $dir . '/Model/AltCoinType.php');

        $mainConfig = new CodeGeneratorConfig($dir, $languageLevel, $namespace);

        $codeGeneratorBuilder = new CodeGeneratorBuilder($mainConfig);

        $codeGenerator = $codeGeneratorBuilder->build();
        foreach ($codeGenerator->generateAllTypes($schema) as $code){}

        $builder = new TypeRegistryGeneratorBuilder($mainConfig);
        $generator = $builder->build();

        $generated = $generator->generate($schema);
        $expected = FixtureLoader::load($expectedFixturePath);
        $this->assertSame($expected, FixtureLoader::normalizeLineEndings($generated));
        FileSystemHelper::rmdir($dir);
        spl_autoload_unregister($myAutoloader);
    }


    /**
     * @return iterable<int, array<int, mixed>>
     */
    public static function dataProviderGeneratePhpCode(): iterable
    {
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

type AltCoin {
    id: ID!
    name: String
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
            __DIR__ . '/fixtures/CodeGeneratorBuilderTest/case-1.php.txt'
            ,];
        yield [
            CodeGeneratorConfig::V8_3,
            BuildSchema::build(Parser::parse(<<<GQL
type Query {
    sum: Int
}
GQL
            )),
            __DIR__ . '/fixtures/CodeGeneratorBuilderTest/case-2.php.txt'
            ,];

        yield [
            CodeGeneratorConfig::V8_3,
            BuildSchema::build(Parser::parse(<<<GQL
type Mutation {
    sum: Int
}
GQL
            )),__DIR__ . '/fixtures/CodeGeneratorBuilderTest/case-3.php.txt'
            ,];
    }
}
