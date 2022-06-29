<?php

require(__DIR__ . '/../vendor/autoload.php');

use Axtiva\FlexibleGraphql\Builder\Foundation\CodeGeneratorBuilder;
use Axtiva\FlexibleGraphql\Builder\Foundation\Psr\Container\TypeRegistryGeneratorBuilder;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\CodeGeneratorConfig;
use Axtiva\FlexibleGraphql\Utils\SchemaBuilder;
use GraphQL\Type\Definition\CustomScalarType;

/** Describe graphql schema */

$schema = SchemaBuilder::build(__DIR__ . '/schema.graphql');

/** Configure TypeRegistry Generator with all resolvers */
$dir = __DIR__ . '/GraphQL';
$namespace = 'Axtiva\FlexibleGraphql\Example\GraphQL';

$builder = new CodeGeneratorBuilder(new CodeGeneratorConfig($dir, CodeGeneratorConfig::V7_4, $namespace));
$codeGenerator = $builder->build();

// Generate all types from schema
foreach ($codeGenerator->generateAllTypes($schema) as $filename);

// Generate directive resolvers
foreach (['plusX', 'uppercase'] as $directiveName) {
    $directive = $schema->getDirective($directiveName);
    foreach($codeGenerator->generateDirectiveResolver($directive, $schema) as $item);
}

// Generate custom scalar
/** @var CustomScalarType $scalar */
$scalar = $schema->getType('DateTime');
$codeGenerator->generateScalarResolver($scalar, $schema);

$builder = new TypeRegistryGeneratorBuilder(
    new CodeGeneratorConfig($dir, CodeGeneratorConfig::V7_4, $namespace)
);
$typeRegistryGenerator = $builder->build();

/** Generate TypeRegistry into file */
file_put_contents(
    $typeRegistryGenerator->getConfig()->getTypeRegistryClassFileName(),
    $typeRegistryGenerator->generate($schema)
);