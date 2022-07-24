<?php

require(__DIR__ . '/../vendor/autoload.php');

use Axtiva\FlexibleGraphql\Builder\Foundation\CodeGeneratorBuilder;
use Axtiva\FlexibleGraphql\Builder\Foundation\CodeGeneratorBuilderFederated;
use Axtiva\FlexibleGraphql\Builder\Foundation\Psr\Container\TypeRegistryGeneratorBuilder;
use Axtiva\FlexibleGraphql\Builder\Foundation\Psr\Container\TypeRegistryGeneratorBuilderFederated;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\CodeGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\LanguageLevelConfigInterface;
use Axtiva\FlexibleGraphql\Utils\FederationV22SchemaExtender;
use Axtiva\FlexibleGraphql\Utils\SchemaBuilder;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\CustomScalarType;

// Describe graphql schema

$schema = FederationV22SchemaExtender::build(
    SchemaBuilder::build(__DIR__ . '/schema.graphql'),
    Parser::parse(file_get_contents(__DIR__ . '/schema.graphql'))
);

// Configure TypeRegistry Generator with all resolvers
$dir = __DIR__ . '/GraphQL';
$namespace = 'Axtiva\FlexibleGraphql\Example\GraphQL';

$mainConfig = new CodeGeneratorConfig($dir, LanguageLevelConfigInterface::V7_4, $namespace);

$builder = new CodeGeneratorBuilderFederated($mainConfig);
$codeGenerator = $builder->build();

// Generate all types from schema
foreach ($codeGenerator->generateAllTypes($schema) as $filename);

// Generate executable directive resolvers
foreach (['plusX', 'uppercase'] as $directiveName) {
    $directive = $schema->getDirective($directiveName);
    foreach($codeGenerator->generateDirectiveResolver($directive, $schema) as $item);
}

// Generate custom scalar
/** @var CustomScalarType $scalar */
$scalar = $schema->getType('DateTime');
$codeGenerator->generateScalarResolver($scalar, $schema);

$builder = new TypeRegistryGeneratorBuilderFederated(
    new CodeGeneratorConfig($dir, CodeGeneratorConfig::V7_4, $namespace)
);
$typeRegistryGenerator = $builder->build();

// Generate TypeRegistry into file
file_put_contents(
    $typeRegistryGenerator->getConfig()->getTypeRegistryClassFileName(),
    $typeRegistryGenerator->generate($schema)
);