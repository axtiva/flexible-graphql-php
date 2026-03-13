<?php

namespace Axtiva\FlexibleGraphql\Tests\Generator\Model\Psr4;

use Axtiva\FlexibleGraphql\Builder\Foundation\CodeGeneratorBuilder;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\CodeGeneratorConfig;
use Axtiva\FlexibleGraphql\Resolver\UnionResolveTypeInterface;
use Axtiva\FlexibleGraphql\Tests\Helper\FixtureLoader;
use Axtiva\FlexibleGraphql\Tests\Helper\FileSystemHelper;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\UnionType;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class UnionResolveTypeModelGeneratorTest extends TestCase
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
            'Currency',
            CodeGeneratorConfig::V8_3,
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
            __DIR__ . '/fixtures/UnionResolveTypeModelGeneratorTest/case-1.php.txt'
            ,];
    }
}
