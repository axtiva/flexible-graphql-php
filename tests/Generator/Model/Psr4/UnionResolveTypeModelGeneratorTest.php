<?php

namespace Axtiva\FlexibleGraphql\Tests\Generator\Model\Psr4;

use Axtiva\FlexibleGraphql\Builder\Foundation\CodeGeneratorBuilder;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\CodeGeneratorConfig;
use Axtiva\FlexibleGraphql\Resolver\UnionResolveTypeInterface;
use Axtiva\FlexibleGraphql\Tests\Helper\FileSystemHelper;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\UnionType;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use PHPUnit\Framework\TestCase;

class UnionResolveTypeModelGeneratorTest extends TestCase
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

        /** @var UnionType $type */
        $type = $schema->getType($typeName);
        $this->assertInstanceOf(UnionType::class, $type);

        foreach ($generator->generateType($type, $schema) as $item) {
            $interfaces = class_implements($item->getClassname());
            if ($interfaces && in_array(UnionResolveTypeInterface::class, $interfaces)) {
                $code = $item;
                break;
            }
        }
        $this->assertTrue(isset($code));
        $this->assertEquals($expected, file_get_contents($code->getFilename()));

        FileSystemHelper::rmdir($dir);
    }

    public function dataProviderGeneratePhpCode(): iterable
    {
        yield [
            'Currency',
            CodeGeneratorConfig::V7_4,
            BuildSchema::build(Parser::parse(<<<GQL
type NamedCurrency {
    id: ID!
    name: String!
}
type CodedCurrency {
    id: ID!
    code: Int!
}
union Currency = NamedCurrency | CodedCurrency
GQL
            )),
            <<<'PHP'
<?php

declare (strict_types=1);
namespace Axtiva\FlexibleGraphql\Example\GraphQL\UnionResolveType;

use GraphQL\Type\Definition\ResolveInfo;
use Axtiva\FlexibleGraphql\Resolver\UnionResolveTypeInterface;
use Axtiva\FlexibleGraphql\Example\GraphQL\Model\NamedCurrencyType;
use Axtiva\FlexibleGraphql\Example\GraphQL\Model\CodedCurrencyType;

/**
 * This code is @generated by axtiva/flexible-graphql-php
 * and will be regenerated. Do not edit it manually
 */
final class CurrencyTypeResolver implements UnionResolveTypeInterface
{
    public function __invoke($model, $context, ResolveInfo $info)
    {
        if (isset($model)) {
            switch (get_class($model)) {
                case NamedCurrencyType::class:
                    return $info->schema->getType('NamedCurrency');
                case CodedCurrencyType::class:
                    return $info->schema->getType('CodedCurrency');
            }
        }
        return null;
    }
}
PHP
            ,];
    }
}