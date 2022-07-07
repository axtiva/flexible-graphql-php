<?php

namespace Axtiva\FlexibleGraphql\Tests\Generator\Model\Psr4;

use Axtiva\FlexibleGraphql\Builder\Foundation\CodeGeneratorBuilder;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\CodeGeneratorConfig;
use Axtiva\FlexibleGraphql\Tests\Helper\FileSystemHelper;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use PHPUnit\Framework\TestCase;

/**
 * @property string $hello
 */
class DemoAccess extends \ArrayObject {}

$demo = new DemoAccess([]);


class InputObjectModelGeneratorTest extends TestCase
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

        /** @var InputObjectType $type */
        $type = $schema->getType($typeName);
        $this->assertInstanceOf(InputObjectType::class, $type);

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
            'TestInput',
            CodeGeneratorConfig::V7_4,
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
            )),<<<'PHP'
<?php

declare (strict_types=1);
namespace Axtiva\FlexibleGraphql\Example\GraphQL\Model;

use Axtiva\FlexibleGraphql\Type\InputType;

/**
 * This code is @generated by axtiva/flexible-graphql-php do not edit it
 * PHP representation of graphql type TestInput
 * @property string $key 
 * @property int $value 
 * @property null|DemoEnumEnum $demoEnum = null 
 * @property DemoInputInputType $demoInput 
 */
final class TestInputInputType extends InputType
{
    protected function decorate($name, $value)
    {
        if ($value === null) {
            return null;
        }

        if ($name === 'demoEnum') {
            return new DemoEnumEnum($value);
        }

        if ($name === 'demoInput') {
            return new DemoInputInputType($value);
        }

        return $value;
    }
}
PHP
            ,];
        yield [
            'TestInput',
            CodeGeneratorConfig::V7_4,
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
            )),<<<'PHP'
<?php

declare (strict_types=1);
namespace Axtiva\FlexibleGraphql\Example\GraphQL\Model;

use Axtiva\FlexibleGraphql\Type\InputType;

/**
 * This code is @generated by axtiva/flexible-graphql-php do not edit it
 * PHP representation of graphql type TestInput
 * @property string $key 
 * @property int $value 
 * @property null|iterable|DemoEnumEnum[] $demoEnum = null 
 * @property null|iterable|DemoInputInputType[][] $demoInput = null 
 */
final class TestInputInputType extends InputType
{
    protected function decorate($name, $value)
    {
        if ($value === null) {
            return null;
        }

        if ($name === 'demoEnum') {
            return (function($value) {foreach($value as $v) yield ($v === null ? null : new DemoEnumEnum($v)); })($value);
        }

        if ($name === 'demoInput') {
            return (function($value) {foreach($value as $v) yield (function($value) {foreach($value as $v) yield ($v === null ? null : new DemoInputInputType($v)); })($v); })($value);
        }

        return $value;
    }
}
PHP
            ,];
    }
}