<?php

namespace Axtiva\FlexibleGraphql\Tests\Generator\Model\Psr4;

use Axtiva\FlexibleGraphql\Builder\Foundation\CodeGeneratorBuilder;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\CodeGeneratorConfig;
use Axtiva\FlexibleGraphql\Tests\Helper\FileSystemHelper;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use PHPUnit\Framework\TestCase;

class ArgsDirectiveResolverModelGeneratorTest extends TestCase
{
    /**
     * @return void
     * @dataProvider dataProviderGeneratePhpCode
     */
    public function testGeneratePhpCode(
        string $directiveName,
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

        /** @var Directive $type */
        $type = $schema->getDirective($directiveName);
        $this->assertInstanceOf(Directive::class, $type);

        foreach ($generator->generateDirectiveResolver($type, $schema) as $item) {
            $code = $item;
        }
        $this->assertTrue(isset($code));
        $this->assertEquals($expected, file_get_contents($code->getFilename()));

        FileSystemHelper::rmdir($dir);
    }

    public function dataProviderGeneratePhpCode(): iterable
    {
        require_once __DIR__ . '/resources/DateTimeScalar.php';
        yield [
            'sum',
            CodeGeneratorConfig::V7_4,
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
            )),<<<'PHP'
<?php

declare (strict_types=1);
namespace Axtiva\FlexibleGraphql\Example\GraphQL\DirectiveArgs;

use Axtiva\FlexibleGraphql\Type\InputType;
use Axtiva\FlexibleGraphql\Example\GraphQL\Model\DemoInputInputType;
use Axtiva\FlexibleGraphql\Example\GraphQL\Model\DemoEnumEnum;
use DateTimeImmutable;

/**
 * This code is @generated by axtiva/flexible-graphql-php do not edit it
 * PHP representation of graphql directive args of sum
 * @property null|int $x = null 
 * @property DemoInputInputType $testInput 
 * @property null|DemoEnumEnum $demo = null 
 * @property null|DateTimeImmutable $date = null 
 * @property mixed $hello = null 
 */
final class SumDirectiveArgs extends InputType
{
    protected function decorate($name, $value)
    {
        if ($value === null) {
            return null;
        }

        if ($name === 'testInput') {
            return new DemoInputInputType($value);
        }

        if ($name === 'demo') {
            return new DemoEnumEnum($value);
        }

        return $value;
    }
}
PHP
            ,];
        yield [
            'sum',
            CodeGeneratorConfig::V7_4,
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
            )),<<<'PHP'
<?php

declare (strict_types=1);
namespace Axtiva\FlexibleGraphql\Example\GraphQL\DirectiveArgs;

use Axtiva\FlexibleGraphql\Type\InputType;
use Axtiva\FlexibleGraphql\Example\GraphQL\Model\DemoInputInputType;
use Axtiva\FlexibleGraphql\Example\GraphQL\Model\DemoEnumEnum;
use DateTimeImmutable;

/**
 * This code is @generated by axtiva/flexible-graphql-php do not edit it
 * PHP representation of graphql directive args of sum
 * @property null|iterable|int[][] $x = null 
 * @property iterable|DemoInputInputType[][][][] $testInput 
 * @property null|DemoEnumEnum $demo = null 
 * @property null|DateTimeImmutable $date = null 
 * @property mixed $hello = null 
 * @property null|iterable[] $hello2 = null 
 */
final class SumDirectiveArgs extends InputType
{
    protected function decorate($name, $value)
    {
        if ($value === null) {
            return null;
        }

        if ($name === 'testInput') {
            return (function($value) {foreach($value as $v) yield (function($value) {foreach($value as $v) yield (function($value) {foreach($value as $v) yield (function($value) {foreach($value as $v) yield ($v === null ? null : new DemoInputInputType($v)); })($v); })($v); })($v); })($value);
        }

        if ($name === 'demo') {
            return new DemoEnumEnum($value);
        }

        return $value;
    }
}
PHP
            ,];
        yield [
            'sum',
            CodeGeneratorConfig::V7_4,
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
            )),<<<'PHP'
<?php

declare (strict_types=1);
namespace Axtiva\FlexibleGraphql\Example\GraphQL\DirectiveArgs;

use Axtiva\FlexibleGraphql\Type\InputType;
use Axtiva\FlexibleGraphql\Example\GraphQL\Model\DemoInputInputType;
use Axtiva\FlexibleGraphql\Example\GraphQL\Model\DemoEnumEnum;
use DateTimeImmutable;

/**
 * This code is @generated by axtiva/flexible-graphql-php do not edit it
 * PHP representation of graphql directive args of sum
 * @property null|iterable|int[][] $x = null 
 * @property iterable|DemoInputInputType[] $testInput 
 * @property null|DemoEnumEnum $demo = null 
 * @property null|DateTimeImmutable $date = null 
 * @property mixed $hello = null 
 * @property null|iterable[] $hello2 = null 
 */
final class SumDirectiveArgs extends InputType
{
    protected function decorate($name, $value)
    {
        if ($value === null) {
            return null;
        }

        if ($name === 'testInput') {
            return (function($value) {foreach($value as $v) yield ($v === null ? null : new DemoInputInputType($v)); })($value);
        }

        if ($name === 'demo') {
            return new DemoEnumEnum($value);
        }

        return $value;
    }
}
PHP
            ,];
    }
}