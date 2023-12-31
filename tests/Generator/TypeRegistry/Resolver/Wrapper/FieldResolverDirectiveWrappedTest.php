<?php

namespace Axtiva\FlexibleGraphql\Tests\Generator\TypeRegistry\Resolver\Wrapper;

use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\ArgsDirectiveResolverGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\ArgsFieldResolverGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\CodeGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\DirectiveResolverGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\FieldResolverGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\ResolverProvider\Foundation\ContainerCallDirectiveResolverProvider;
use Axtiva\FlexibleGraphql\Generator\ResolverProvider\Foundation\ContainerCallFieldResolverProvider;
use Axtiva\FlexibleGraphql\Generator\ResolverProvider\Foundation\WrappedContainerCallFieldResolverProvider;
use Axtiva\FlexibleGraphql\Generator\Serializer\Foundation\VariableSerializer;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\Resolver\DefaultResolver\FieldGenerator as DefaultFieldGenerator;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\Resolver\Psr\Container\FieldGenerator;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\Resolver;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\Resolver\Wrapper\FieldResolverDirectiveWrapped;
use Axtiva\FlexibleGraphql\Tests\Helper\FileSystemHelper;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use PHPUnit\Framework\TestCase;

class FieldResolverDirectiveWrappedTest extends TestCase
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
        $dir = uniqid('/tmp/TmpTestData/GraphQL');

        FileSystemHelper::rmdir($dir);
        FileSystemHelper::mkdir($dir);

        $mainConfig = new CodeGeneratorConfig($dir, $languageLevel, $namespace);
        $fieldConfig = new FieldResolverGeneratorConfig($mainConfig);
        $argsFieldConfig = new ArgsFieldResolverGeneratorConfig($mainConfig);
        $argsDirectiveConfig = new ArgsDirectiveResolverGeneratorConfig($mainConfig);
        $directiveConfig = new DirectiveResolverGeneratorConfig($mainConfig);
        $defaultFieldGenerator = new DefaultFieldGenerator();
        $fieldGenerator = new FieldGenerator(
            $fieldConfig,
            new WrappedContainerCallFieldResolverProvider(
                new ContainerCallFieldResolverProvider(),
                $argsFieldConfig
            ),
        );
        $directiveResolver = new Resolver\Psr\Container\DirectiveGenerator(
            $directiveConfig,
            new ContainerCallDirectiveResolverProvider()
        );

        $generator = new FieldResolverDirectiveWrapped(
            new VariableSerializer(),
            $fieldGenerator,
            $defaultFieldGenerator,
            $directiveResolver,
            $argsDirectiveConfig
        );

        /** @var ObjectType $type */
        $type = $schema->getType($typeName);
        $this->assertInstanceOf(ObjectType::class, $type);

        $this->assertNotFalse($type->hasField($fieldName));
        $field = $type->getField($fieldName);

        $this->assertEquals($expected, $generator->generate($type, $field));

        FileSystemHelper::rmdir($dir);
    }

    public function dataProviderGeneratePhpCode(): iterable
    {

        require_once __DIR__ . '/../../../ResolverProvider/resources/NameResolverArgs.php';
        require_once __DIR__ . '/../../resources/NameResolver.php';
        yield [
            'NamedCurrency',
            'hello',
            CodeGeneratorConfig::V7_4,
            BuildSchema::build(Parser::parse(<<<GQL
directive @sum(x: Int) on FIELD | FIELD_DEFINITION
directive @uppercase on FIELD | FIELD_DEFINITION
type NamedCurrency {
    id: ID!
    hello(x: Int, testInput: DemoInput!, demo: DemoEnum, date: DateTime, hello: HelloScalar): String @uppercase
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
GQL)),
            <<<'PHP'
function($rootValue, $args, $context, $info) {
                        return $this->container->get('Axtiva\FlexibleGraphql\Example\GraphQL\Directive\UppercaseDirective')(
                        \Axtiva\FlexibleGraphql\Resolver\Foundation\DefaultResolver::getInstance(), 
                        array (
),
                        $rootValue, $args, $context, $info
                        );
                    }
PHP,
        ];

        require_once __DIR__ . '/../../../ResolverProvider/resources/NameResolverArgs.php';
        require_once __DIR__ . '/../../resources/NameResolver.php';
        yield [
            'NamedCurrency',
            'name',
            CodeGeneratorConfig::V7_4,
            BuildSchema::build(Parser::parse(<<<GQL
directive @sum(x: Int) on FIELD | FIELD_DEFINITION
directive @uppercase on FIELD | FIELD_DEFINITION
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
GQL)),
            <<<'PHP'
(function ($rootValue, $args, $context, $info) {
    $args = new \Axtiva\FlexibleGraphql\Example\GraphQL\ResolverArgs\NamedCurrency\NameResolverArgs($args);
    return $this->container->get('Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\NamedCurrency\NameResolver')($rootValue, $args, $context, $info);
})
PHP,
        ];

        yield [
            'NamedCurrency',
            'demo',
            CodeGeneratorConfig::V7_4,
            BuildSchema::build(Parser::parse(<<<GQL
directive @sum(x: Int) on FIELD | FIELD_DEFINITION
directive @uppercase on FIELD | FIELD_DEFINITION
type NamedCurrency {
    id: ID!
    demo(x: Int, testInput: DemoInput!, demo: DemoEnum, date: DateTime, hello: HelloScalar): String
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
GQL)),
            <<<'PHP'
\Axtiva\FlexibleGraphql\Resolver\Foundation\DefaultResolver::getInstance()
PHP,
        ];

        require_once __DIR__ . '/../../../ResolverProvider/resources/NameResolverArgs.php';
        require_once __DIR__ . '/../../resources/NameResolver.php';
        require_once __DIR__ . '/../../resources/SumDirective.php';
        require_once __DIR__ . '/../../resources/SumDirectiveArgs.php';
        yield [
            'NamedCurrency',
            'name',
            CodeGeneratorConfig::V7_4,
            BuildSchema::build(Parser::parse(<<<GQL
directive @sum(x: Int) on FIELD | FIELD_DEFINITION
type NamedCurrency {
    id: ID!
    name(x: Int, testInput: DemoInput!, demo: DemoEnum, date: DateTime, hello: HelloScalar): String @sum(x: 2)
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
GQL)),
            <<<'PHP'
function($rootValue, $args, $context, $info) {
                        return $this->container->get('Axtiva\FlexibleGraphql\Example\GraphQL\Directive\SumDirective')(
                        (function ($rootValue, $args, $context, $info) {
    $args = new \Axtiva\FlexibleGraphql\Example\GraphQL\ResolverArgs\NamedCurrency\NameResolverArgs($args);
    return $this->container->get('Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\NamedCurrency\NameResolver')($rootValue, $args, $context, $info);
}), 
                        new \Axtiva\FlexibleGraphql\Example\GraphQL\DirectiveArgs\SumDirectiveArgs(array (
  'x' => '2',
)),
                        $rootValue, $args, $context, $info
                        );
                    }
PHP,
        ];

        require_once __DIR__ . '/../../../ResolverProvider/resources/NameResolverArgs.php';
        require_once __DIR__ . '/../../resources/NameResolver.php';
        require_once __DIR__ . '/../../resources/SumDirective.php';
        require_once __DIR__ . '/../../resources/SumDirectiveArgs.php';
        yield [
            'NamedCurrency',
            'name',
            CodeGeneratorConfig::V7_4,
            BuildSchema::build(Parser::parse(<<<GQL
directive @sum(x: Int) on FIELD | FIELD_DEFINITION
directive @uppercase on FIELD | FIELD_DEFINITION
type NamedCurrency {
    id: ID!
    name(x: Int, testInput: DemoInput!, demo: DemoEnum, date: DateTime, hello: HelloScalar): String @uppercase @sum(x: 2)
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
GQL)),
            <<<'PHP'
function($rootValue, $args, $context, $info) {
                        return $this->container->get('Axtiva\FlexibleGraphql\Example\GraphQL\Directive\SumDirective')(
                        function($rootValue, $args, $context, $info) {
                        return $this->container->get('Axtiva\FlexibleGraphql\Example\GraphQL\Directive\UppercaseDirective')(
                        (function ($rootValue, $args, $context, $info) {
    $args = new \Axtiva\FlexibleGraphql\Example\GraphQL\ResolverArgs\NamedCurrency\NameResolverArgs($args);
    return $this->container->get('Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\NamedCurrency\NameResolver')($rootValue, $args, $context, $info);
}), 
                        array (
),
                        $rootValue, $args, $context, $info
                        );
                    }, 
                        new \Axtiva\FlexibleGraphql\Example\GraphQL\DirectiveArgs\SumDirectiveArgs(array (
  'x' => '2',
)),
                        $rootValue, $args, $context, $info
                        );
                    }
PHP,
        ];
        require_once __DIR__ . '/../../resources/SumVariantsDirective.php';
        require_once __DIR__ . '/../../resources/SumVariantsDirectiveArgs.php';
        yield [
            'NamedCurrency',
            'name',
            CodeGeneratorConfig::V7_4,
            BuildSchema::build(Parser::parse(<<<GQL
directive @sumVariants(x: Int, variants: [Int]) on FIELD | FIELD_DEFINITION
directive @uppercase on FIELD | FIELD_DEFINITION
type NamedCurrency {
    id: ID!
    name(x: Int, testInput: DemoInput!, demo: DemoEnum, date: DateTime, hello: HelloScalar): String @uppercase @sumVariants(x: 2, variants: [1,2,null,3])
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
GQL)),
            <<<'PHP'
function($rootValue, $args, $context, $info) {
                        return $this->container->get('Axtiva\FlexibleGraphql\Example\GraphQL\Directive\SumVariantsDirective')(
                        function($rootValue, $args, $context, $info) {
                        return $this->container->get('Axtiva\FlexibleGraphql\Example\GraphQL\Directive\UppercaseDirective')(
                        (function ($rootValue, $args, $context, $info) {
    $args = new \Axtiva\FlexibleGraphql\Example\GraphQL\ResolverArgs\NamedCurrency\NameResolverArgs($args);
    return $this->container->get('Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\NamedCurrency\NameResolver')($rootValue, $args, $context, $info);
}), 
                        array (
),
                        $rootValue, $args, $context, $info
                        );
                    }, 
                        new \Axtiva\FlexibleGraphql\Example\GraphQL\DirectiveArgs\SumVariantsDirectiveArgs(array (
  'x' => '2',
  'variants' => 
  array (
    0 => '1',
    1 => '2',
    2 => NULL,
    3 => '3',
  ),
)),
                        $rootValue, $args, $context, $info
                        );
                    }
PHP,
        ];
    }
}