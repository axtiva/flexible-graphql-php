<?php

require(__DIR__ . '/../vendor/autoload.php');

use Axtiva\FlexibleGraphql\Builder\Foundation\Psr\Container\TypeRegistryGeneratorBuilder;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\CodeGeneratorConfig;
use Axtiva\FlexibleGraphql\Utils\SchemaBuilder;
use GraphQL\Utils\BuildSchema;

/** Describe graphql schema */

$schema = SchemaBuilder::build(__DIR__ . '/schema.graphql');

/** Configure TypeRegistry Generator with all resolvers */
$dir = __DIR__ . '/GraphQL';
$namespace = 'Axtiva\FlexibleGraphql\Example\GraphQL';
$builder = new TypeRegistryGeneratorBuilder(
    $dir,
    $namespace
);

$generator = $builder->build();

/** Generate TypeRegistry into file */
file_put_contents(
    $generator->getConfig()->getTypeRegistryClassFileName(),
    $generator->generate($schema)
);