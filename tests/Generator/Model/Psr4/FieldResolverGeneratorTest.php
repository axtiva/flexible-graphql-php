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

class FieldResolverGeneratorTest extends TestCase
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
        $this->assertEquals($expected, file_get_contents($code->getFilename()));

        FileSystemHelper::rmdir($dir);
    }


    public function dataProviderGeneratePhpCode(): iterable
    {
        require_once __DIR__ . '/resources/DateTimeScalar.php';
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
GQL
            )),
            <<<'PHP'
<?php

declare (strict_types=1);
namespace Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\NamedCurrency;

use Axtiva\FlexibleGraphql\Generator\Exception\NotImplementedResolver;
use GraphQL\Type\Definition\ResolveInfo;
use Axtiva\FlexibleGraphql\Resolver\ResolverInterface;
use Axtiva\FlexibleGraphql\Example\GraphQL\ResolverArgs\NamedCurrency\NameResolverArgs;
use Axtiva\FlexibleGraphql\Example\GraphQL\Model\NamedCurrencyType;

/**
 * This code is @generated by axtiva/flexible-graphql-php
 * This is resolver for NamedCurrency.name
 */
final class NameResolver implements ResolverInterface
{
    /**
     * @param NamedCurrencyType $rootValue
     * @param NameResolverArgs $args
     * @param $context
     * @param ResolveInfo $info
     * @return ?string
     */
    public function __invoke($rootValue, $args, $context, ResolveInfo $info)
    {
        throw new NotImplementedResolver('Not implemented field resolver ' . __CLASS__);
    }
}
PHP
            ,];

        require_once __DIR__ . '/resources/DateTimeScalar.php';
        yield [
            'NamedCurrency',
            'date',
            CodeGeneratorConfig::V7_4,
            BuildSchema::build(Parser::parse(<<<GQL
type NamedCurrency {
    id: ID!
    date: DateTime!
}
scalar DateTime
GQL
            )),<<<'PHP'
<?php

declare (strict_types=1);
namespace Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\NamedCurrency;

use Axtiva\FlexibleGraphql\Generator\Exception\NotImplementedResolver;
use GraphQL\Type\Definition\ResolveInfo;
use Axtiva\FlexibleGraphql\Resolver\ResolverInterface;
use Axtiva\FlexibleGraphql\Example\GraphQL\Model\NamedCurrencyType;
use DateTimeImmutable;

/**
 * This code is @generated by axtiva/flexible-graphql-php
 * This is resolver for NamedCurrency.date
 */
final class DateResolver implements ResolverInterface
{
    /**
     * @param NamedCurrencyType $rootValue
     * @param $args
     * @param $context
     * @param ResolveInfo $info
     * @return DateTimeImmutable
     */
    public function __invoke($rootValue, $args, $context, ResolveInfo $info)
    {
        throw new NotImplementedResolver('Not implemented field resolver ' . __CLASS__);
    }
}
PHP
            ,];
    }
}