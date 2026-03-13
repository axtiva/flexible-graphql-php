<?php

namespace Axtiva\FlexibleGraphql\Tests\Generator\Model\Psr4;

use Axtiva\FlexibleGraphql\Builder\Foundation\CodeGeneratorBuilder;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\CodeGeneratorConfig;
use Axtiva\FlexibleGraphql\Tests\Helper\FixtureLoader;
use Axtiva\FlexibleGraphql\Tests\Helper\FileSystemHelper;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @property string $hello
 */
/**
 * @extends \ArrayObject<int, mixed>
 */
class DemoAccess extends \ArrayObject {}

$demo = new DemoAccess([]);


class InputObjectModelGeneratorTest extends TestCase
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

        /** @var InputObjectType $type */
        $type = $schema->getType($typeName);
        $this->assertInstanceOf(InputObjectType::class, $type);

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
            'TestInput',
            CodeGeneratorConfig::V8_3,
            BuildSchema::build(Parser::parse(<<<GQL
input TestInput {
    key: String!
    value: Int! = 5
    demoEnum: DemoEnum
    demoInput: DemoInput!
}

enum DemoEnum {
    A
    B
}

input DemoInput {
  field: Int
}
GQL
            )),__DIR__ . '/fixtures/InputObjectModelGeneratorTest/case-1.php.txt'
            ,];
        yield [
            'TestInput',
            CodeGeneratorConfig::V8_3,
            BuildSchema::build(Parser::parse(<<<GQL
input TestInput {
    key: String!
    value: Int! = 5
    demoEnum: [DemoEnum]
    demoInput: [[DemoInput!]]
}

enum DemoEnum {
    A
    B
}

input DemoInput {
  field: Int
}
GQL
            )),__DIR__ . '/fixtures/InputObjectModelGeneratorTest/case-2.php.txt'
            ,];
    }
}
