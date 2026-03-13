<?php

namespace Axtiva\FlexibleGraphql\Tests\Generator\Model\Psr4;

use Axtiva\FlexibleGraphql\Builder\Foundation\CodeGeneratorBuilder;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\CodeGeneratorConfig;
use Axtiva\FlexibleGraphql\Tests\Helper\FixtureLoader;
use Axtiva\FlexibleGraphql\Tests\Helper\FileSystemHelper;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FieldResolverGeneratorTest extends TestCase
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

        $builder = new CodeGeneratorBuilder(new CodeGeneratorConfig($dir, $languageLevel, $namespace));

        $generator = $builder->build();

        /** @var ObjectType $type */
        $type = $schema->getType($typeName);
        $this->assertInstanceOf(ObjectType::class, $type);

        $this->assertNotFalse($type->hasField($fieldName));
        $field = $type->getField($fieldName);
        foreach ($generator->generateFieldResolver($type, $field, $schema) as $item) {
            $code = $item;
            break;
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
GQL
            )),
            __DIR__ . '/fixtures/FieldResolverGeneratorTest/case-1.php.txt'
            ,];

        require_once __DIR__ . '/resources/DateTimeScalar.php';
        yield [
            'NamedCurrency',
            'date',
            CodeGeneratorConfig::V8_3,
            BuildSchema::build(Parser::parse(<<<GQL
type NamedCurrency {
    id: ID!
    date: DateTime!
}
scalar DateTime
GQL
            )),
            __DIR__ . '/fixtures/FieldResolverGeneratorTest/case-2.php.txt'
            ,];

        require_once __DIR__ . '/resources/DateTimeScalar.php';
        yield [
            'NamedCurrency',
            'dates',
            CodeGeneratorConfig::V8_3,
            BuildSchema::build(Parser::parse(<<<GQL
type NamedCurrency {
    id: ID!
    dates: [DateTime!]
}
scalar DateTime
GQL
            )),
            __DIR__ . '/fixtures/FieldResolverGeneratorTest/case-3.php.txt'
            ,];
    }
}
