<?php

namespace Axtiva\FlexibleGraphql\Tests\Generator\Model\Psr4;

use Axtiva\FlexibleGraphql\Builder\Foundation\CodeGeneratorBuilder;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\CodeGeneratorConfig;
use Axtiva\FlexibleGraphql\Tests\Helper\FixtureLoader;
use Axtiva\FlexibleGraphql\Tests\Helper\FileSystemHelper;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ArgsDirectiveResolverModelGeneratorTest extends TestCase
{
    /**
     * @return void
     * @dataProvider dataProviderGeneratePhpCode
     */
#[DataProvider('dataProviderGeneratePhpCode')]
    public function testGeneratePhpCode(
        string $directiveName,
        string $languageLevel,
        Schema $schema,
        string $expectedFixturePath
    ) {
        $namespace = 'Axtiva\FlexibleGraphql\Example\GraphQL';
        $dir = uniqid('/tmp/TmpTestData/GraphQL');

        FileSystemHelper::rmdir($dir);
        FileSystemHelper::mkdir($dir);

        $builder = new CodeGeneratorBuilder(new CodeGeneratorConfig($dir, $languageLevel, $namespace));

        $generator = $builder->build();

        /** @var Directive $type */
        $type = $schema->getDirective($directiveName);
        $this->assertInstanceOf(Directive::class, $type);

        foreach ($generator->generateDirectiveResolver($type, $schema) as $item) {
            $code = $item;
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
        require_once __DIR__ . '/resources/DateTimeScalar.php';
        yield [
            'sum',
            CodeGeneratorConfig::V8_3,
            BuildSchema::build(Parser::parse(<<<GQL
directive @sum(x: Int, testInput: DemoInput!, demo: DemoEnum, date: DateTime, hello: HelloScalar) on FIELD | FIELD_DEFINITION

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
            )),__DIR__ . '/fixtures/ArgsDirectiveResolverModelGeneratorTest/case-1.php.txt'
            ,];
        yield [
            'sum',
            CodeGeneratorConfig::V8_3,
            BuildSchema::build(Parser::parse(<<<GQL
directive @sum(x: [[Int]], testInput: [[[[DemoInput!]]]]!, demo: DemoEnum, date: DateTime, hello: HelloScalar, hello2: [[HelloScalar]]) on FIELD | FIELD_DEFINITION

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
            )),__DIR__ . '/fixtures/ArgsDirectiveResolverModelGeneratorTest/case-2.php.txt'
            ,];
        yield [
            'sum',
            CodeGeneratorConfig::V8_3,
            BuildSchema::build(Parser::parse(<<<GQL
directive @sum(x: [[Int]], testInput: [DemoInput!]!, demo: DemoEnum, date: DateTime, hello: HelloScalar, hello2: [[HelloScalar]]) on FIELD | FIELD_DEFINITION

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
            )),__DIR__ . '/fixtures/ArgsDirectiveResolverModelGeneratorTest/case-3.php.txt'
            ,];
    }
}
