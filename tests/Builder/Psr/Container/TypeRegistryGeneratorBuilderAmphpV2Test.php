<?php

namespace Axtiva\FlexibleGraphql\Tests\Builder\Psr\Container;

use Axtiva\FlexibleGraphql\Builder\Foundation\Psr\Container\TypeRegistryGeneratorBuilder;
use Axtiva\FlexibleGraphql\Builder\Foundation\Psr\Container\TypeRegistryGeneratorBuilderAmphp;
use Axtiva\FlexibleGraphql\Builder\Foundation\Psr\Container\TypeRegistryGeneratorBuilderAmphpV2;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\CodeGeneratorConfig;
use Axtiva\FlexibleGraphql\Tests\Helper\FileSystemHelper;
use GraphQL\Language\Parser;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use PHPUnit\Framework\TestCase;

class TypeRegistryGeneratorBuilderAmphpV2Test extends TestCase
{
    /**
     * @return void
     * @dataProvider dataProviderGeneratePhpCode
     */
    public function testGeneratePhpCode(
        string $languageLevel,
        Schema $schema,
        string $expected
    ) {
        $namespace = 'Axtiva\FlexibleGraphql\Example\GraphQL';
        $dir = uniqid('/tmp/TmpTestData/GraphQL');

        FileSystemHelper::rmdir($dir);
        FileSystemHelper::mkdir($dir);

        $mainConfig = new CodeGeneratorConfig($dir, $languageLevel, $namespace);

        $baseBuilder = new TypeRegistryGeneratorBuilder($mainConfig);
        $builder = new TypeRegistryGeneratorBuilderAmphpV2($baseBuilder);
        $generator = $builder->build();

        $this->assertEquals($expected, $generator->generate($schema));

        FileSystemHelper::rmdir($dir);
    }

    public function dataProviderGeneratePhpCode(): iterable
    {
        require_once __DIR__ . '/../../../Generator/ResolverProvider/resources/NameResolverArgs.php';
        require_once __DIR__ . '/../../../Generator/TypeRegistry/resources/NameResolver.php';
        require_once __DIR__ . '/../../../Generator/TypeRegistry/resources/SumDirective.php';
        require_once __DIR__ . '/../../../Generator/TypeRegistry/resources/SumDirectiveArgs.php';
        yield [
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
GQL
            )),
            <<<'PHP'
<?php
/**
 * Autogenerated file by axtiva/flexible-graphql-php Do not edit it manually
 */ 
namespace Axtiva\FlexibleGraphql\Example\GraphQL;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use Axtiva\FlexibleGraphql\Type\EnumType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Type\Definition\UnionType;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\Argument;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectField;
use Psr\Container\ContainerInterface;
use GraphQL\Type\Schema;

class TypeRegistry
{
    private ContainerInterface $container;
    
    /**
     * @var array<string, Type>
     */
    private array $types = [];
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getType(string $name): Type
    {
        return $this->types[$name] ??= $this->{$name}();
    }
    
    
            public function NamedCurrency()
            {
                return new ObjectType([
            'name' => 'NamedCurrency',
            'description' => NULL,
            'fields' => fn() => ['id' => new FieldDefinition([
            'name' => 'id',
            'description' => NULL,
            'deprecationReason' => NULL,
            // No resolver. Default used
            'type' => function() { return Type::nonNull(function() { return Type::id(); }); },
            'args' => [],
        ]),'name' => new FieldDefinition([
            'name' => 'name',
            'description' => NULL,
            'deprecationReason' => NULL,
            'resolve' => (function($rootValue, $args, $context, $info) {
                    return \Amp\call(function($rootValue, $args, $context, $info) {
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
                    }, $rootValue, $args, $context, $info);
                }),
            'type' => function() { return Type::string(); },
            'args' => ['x' => [
            'name' => 'x',
            'type' => function() { return Type::int(); },
        ],'testInput' => [
            'name' => 'testInput',
            'type' => function() { return Type::nonNull(function() { return $this->getType('DemoInput'); }); },
        ],'demo' => [
            'name' => 'demo',
            'type' => function() { return $this->getType('DemoEnum'); },
        ],'date' => [
            'name' => 'date',
            'type' => function() { return $this->getType('DateTime'); },
        ],'hello' => [
            'name' => 'hello',
            'type' => function() { return $this->getType('HelloScalar'); },
        ]],
        ])],
        ]);
            }
        


            public function DemoEnum()
            {
                return new EnumType([
        'name' => 'DemoEnum',
        'description' => NULL,
        'values' => ['A' => [
            'name' => 'A', 
            'value' => 'A',
            'description' => NULL,
            'deprecationReason' => NULL,
            ],
'B' => [
            'name' => 'B', 
            'value' => 'B',
            'description' => NULL,
            'deprecationReason' => NULL,
            ]],
        ]);
            }
        


            public function DemoInput()
            {
                return new InputObjectType([
        'name' => 'DemoInput',
        'description' =>  NULL,
        'fields' => fn() => ['field' => [
            'name' => 'field',
            'type' => Type::int(),
        ]],
        ]);
            }
        


            public function DateTime()
            {
                return new CustomScalarType([
            'name' => 'DateTime',
            'description' => NULL,
            'serialize' => function($value) {return ($this->container->get('Axtiva\FlexibleGraphql\Example\GraphQL\Scalar\DateTimeScalar'))->serialize($value);},
            'parseValue' => function($value) {return ($this->container->get('Axtiva\FlexibleGraphql\Example\GraphQL\Scalar\DateTimeScalar'))->parseValue($value);},
            'parseLiteral' => function($value, $variables) {return ($this->container->get('Axtiva\FlexibleGraphql\Example\GraphQL\Scalar\DateTimeScalar'))->parseLiteral($value, $variables);},
        ]);
            }
        


            public function HelloScalar()
            {
                return new CustomScalarType([
            'name' => 'HelloScalar',
            'description' => NULL,

        ]);
            }
        

    public function Query()
    {
        return new ObjectType(['name' => 'Query']);
    }

    public function Mutation()
    {
        return new ObjectType(['name' => 'Mutation']);
    }


    public function directive_sum()
    {
        static $directive = null;
        if ($directive === null) {
            $directive = new Directive([
            'name' => 'sum',
            'description' => NULL,
            'isRepeatable' => false,
            'locations' => ['FIELD','FIELD_DEFINITION'],
            'args' => [
                [
            'name' => 'x',
            'type' => function() { return Type::int(); },
        ]
            ],
        ]);
        }
        
        return $directive;
    }
        


    public function directive_uppercase()
    {
        static $directive = null;
        if ($directive === null) {
            $directive = new Directive([
            'name' => 'uppercase',
            'description' => NULL,
            'isRepeatable' => false,
            'locations' => ['FIELD','FIELD_DEFINITION'],
            'args' => [
                
            ],
        ]);
        }
        
        return $directive;
    }
        


    public function getDirectives()
    {
        return [$this->directive_sum(),$this->directive_uppercase()];
    }
        

}

PHP
            ,];

        yield [
            CodeGeneratorConfig::V7_4,
            BuildSchema::build(Parser::parse(<<<GQL
type Query {
    sum: Int
}
GQL
            )),
            <<<'PHP'
<?php
/**
 * Autogenerated file by axtiva/flexible-graphql-php Do not edit it manually
 */ 
namespace Axtiva\FlexibleGraphql\Example\GraphQL;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use Axtiva\FlexibleGraphql\Type\EnumType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Type\Definition\UnionType;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\Argument;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectField;
use Psr\Container\ContainerInterface;
use GraphQL\Type\Schema;

class TypeRegistry
{
    private ContainerInterface $container;
    
    /**
     * @var array<string, Type>
     */
    private array $types = [];
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getType(string $name): Type
    {
        return $this->types[$name] ??= $this->{$name}();
    }
    
    
            public function Query()
            {
                return new ObjectType([
            'name' => 'Query',
            'description' => NULL,
            'fields' => fn() => ['sum' => new FieldDefinition([
            'name' => 'sum',
            'description' => NULL,
            'deprecationReason' => NULL,
            'resolve' => (function($rootValue, $args, $context, $info) {
                    return \Amp\call((function ($rootValue, $args, $context, $info) {
    
    return $this->container->get('Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\Query\SumResolver')($rootValue, $args, $context, $info);
}), $rootValue, $args, $context, $info);
                }),
            'type' => function() { return Type::int(); },
            'args' => [],
        ])],
        ]);
            }
        

    public function Mutation()
    {
        return new ObjectType(['name' => 'Mutation']);
    }


    public function getDirectives()
    {
        return [];
    }
        

}

PHP
            ,];

        yield [
            CodeGeneratorConfig::V7_4,
            BuildSchema::build(Parser::parse(<<<GQL
type Mutation {
    sum: Int
}
GQL
            )),<<<'PHP'
<?php
/**
 * Autogenerated file by axtiva/flexible-graphql-php Do not edit it manually
 */ 
namespace Axtiva\FlexibleGraphql\Example\GraphQL;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use Axtiva\FlexibleGraphql\Type\EnumType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Type\Definition\UnionType;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\Argument;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectField;
use Psr\Container\ContainerInterface;
use GraphQL\Type\Schema;

class TypeRegistry
{
    private ContainerInterface $container;
    
    /**
     * @var array<string, Type>
     */
    private array $types = [];
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getType(string $name): Type
    {
        return $this->types[$name] ??= $this->{$name}();
    }
    
    
            public function Mutation()
            {
                return new ObjectType([
            'name' => 'Mutation',
            'description' => NULL,
            'fields' => fn() => ['sum' => new FieldDefinition([
            'name' => 'sum',
            'description' => NULL,
            'deprecationReason' => NULL,
            // No resolver. Default used
            'type' => function() { return Type::int(); },
            'args' => [],
        ])],
        ]);
            }
        

    public function Query()
    {
        return new ObjectType(['name' => 'Query']);
    }


    public function getDirectives()
    {
        return [];
    }
        

}

PHP
            ,];
    }
}