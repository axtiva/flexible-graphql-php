<?php

namespace Axtiva\FlexibleGraphql\Tests\Generator\Model\Psr4;

use Axtiva\FlexibleGraphql\Builder\Foundation\CodeGeneratorBuilder;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\CodeGeneratorConfig;
use Axtiva\FlexibleGraphql\Tests\Helper\FileSystemHelper;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use PHPUnit\Framework\TestCase;

class ObjectModelGeneratorTest extends TestCase
{
    /**
     * @return void
     * @dataProvider dataProviderGeneratePhpCode
     */
    public function testGeneratePhpCode(
        string $typeName,
        string $languageLevel,
        Schema $schema,
        string $expected
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
        $this->assertEquals($expected, file_get_contents($code->getFilename()));

        FileSystemHelper::rmdir($dir);
    }

    public function dataProviderGeneratePhpCode(): iterable
    {
        yield [
            'Account',
            CodeGeneratorConfig::V7_4,
            BuildSchema::build(Parser::parse(<<<GQL
interface Node {
    id: ID!
}

type Account implements Node {
    id: ID!
    number: String!
}
GQL
            )),<<<'PHP'
<?php

declare (strict_types=1);
namespace Axtiva\FlexibleGraphql\Example\GraphQL\Model;

use Axtiva\FlexibleGraphql\Resolver\AutoGenerationInterface;

/**
 * This code is @generated by axtiva/flexible-graphql-php
 * if you want to extend it or change, then remove interface AutoGenerationInterface
 * and it will be managed by you, not axtiva/flexible-graphql-php code generator
 * PHP representation of graphql type Account
 */
final class AccountType implements AutoGenerationInterface, NodeInterface
{
    public string $id;
    public string $number;
}
PHP
            ,];

        require_once __DIR__ . '/resources/DateTimeScalar.php';
        yield [
            'Transaction',
            CodeGeneratorConfig::V7_4,
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
            )),<<<'PHP'
<?php

declare (strict_types=1);
namespace Axtiva\FlexibleGraphql\Example\GraphQL\Model;

use Axtiva\FlexibleGraphql\Resolver\AutoGenerationInterface;
use DateTimeImmutable;

/**
 * This code is @generated by axtiva/flexible-graphql-php
 * if you want to extend it or change, then remove interface AutoGenerationInterface
 * and it will be managed by you, not axtiva/flexible-graphql-php code generator
 * PHP representation of graphql type Transaction
 */
final class TransactionType implements AutoGenerationInterface
{
    public string $id;
    public int $amount;
    public ?string $ups = null;
    public ?DateTimeImmutable $createdAt = null;
    public DateTimeImmutable $createdAtNotNull;
    public TransactionStatusEnum $status;
}
PHP
            ,];
        yield [
            'Transaction',
            CodeGeneratorConfig::V7_4,
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
            )),<<<'PHP'
<?php

declare (strict_types=1);
namespace Axtiva\FlexibleGraphql\Example\GraphQL\Model;

use Axtiva\FlexibleGraphql\Resolver\AutoGenerationInterface;
use DateTimeImmutable;

/**
 * This code is @generated by axtiva/flexible-graphql-php
 * if you want to extend it or change, then remove interface AutoGenerationInterface
 * and it will be managed by you, not axtiva/flexible-graphql-php code generator
 * PHP representation of graphql type Transaction
 */
final class TransactionType implements AutoGenerationInterface
{
    public string $id;
    public int $amount;
    /**
     * demo 
description
     * @deprecation wat!
     */
    public ?string $ups = null;
    /**
     * @var null|iterable[][]
     */
    public ?iterable $hello = null;
    /**
     * @var iterable|DateTimeImmutable[]
     */
    public iterable $createdAt;
    /**
     * @deprecation wat!
     * @var null|iterable|DateTimeImmutable[]
     */
    public ?iterable $createdAtNotNull = null;
    /**
     * demo description
     * @var null|iterable|TransactionStatusEnum[][]
     */
    public ?iterable $status = null;
}
PHP
            ,];
    }
}