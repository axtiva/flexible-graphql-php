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

class ObjectModelGeneratorTest extends TestCase
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
        $dir = uniqid('/tmp/TmpTestData/GraphQL');

        FileSystemHelper::rmdir($dir);
        FileSystemHelper::mkdir($dir);

        $builder = new CodeGeneratorBuilder(new CodeGeneratorConfig($dir, $languageLevel, $namespace));

        $generator = $builder->build();

        /** @var ObjectType $type */
        $type = $schema->getType($typeName);
        $this->assertInstanceOf(ObjectType::class, $type);

        foreach ($generator->generateType($type, $schema) as $item) {
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
        yield [
            'Account',
            CodeGeneratorConfig::V8_3,
            BuildSchema::build(Parser::parse(<<<GQL
interface Node {
    id: ID!
}

type Account implements Node {
    id: ID!
    number: String!
}
GQL
            )),
            __DIR__ . '/fixtures/ObjectModelGeneratorTest/case-1.php.txt'
            ,];

        require_once __DIR__ . '/resources/DateTimeScalar.php';
        yield [
            'Transaction',
            CodeGeneratorConfig::V8_3,
            BuildSchema::build(Parser::parse(<<<GQL
type Transaction {
    id: ID!
    amount: Int!
    ups: String
    createdAt: DateTime
    createdAtNotNull: DateTime!
    status: TransactionStatus!
}
enum TransactionStatus {
    NEW
}
scalar DateTime
GQL
            )),
            __DIR__ . '/fixtures/ObjectModelGeneratorTest/case-2.php.txt'
            ,];
        yield [
            'Transaction',
            CodeGeneratorConfig::V8_3,
            BuildSchema::build(Parser::parse(<<<GQL
type Transaction {
    id: ID!
    amount: Int!
    """
    demo 
    description
    """
    ups: String @deprecated(reason: "wat!")
    hello: [[[HelloWorld]]]
    createdAt: [DateTime]!
    createdAtNotNull: [DateTime!] @deprecated(reason: "wat!")
    "demo description"
    status: [[TransactionStatus!]]
}
enum TransactionStatus {
    NEW
}
scalar DateTime
scalar HelloWorld
GQL
            )),
            __DIR__ . '/fixtures/ObjectModelGeneratorTest/case-3.php.txt'
            ,];
    }
}
