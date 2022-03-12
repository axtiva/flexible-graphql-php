<?php

require(__DIR__ . '/../vendor/autoload.php');

use Axtiva\FlexibleGraphql\Builder\Foundation\CodeGeneratorBuilder;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\CodeGeneratorConfig;
use Axtiva\FlexibleGraphql\Utils\SchemaBuilder;
use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Type\Definition\ObjectType;

$modelClassNamespace = 'Axtiva\FlexibleGraphql\Example\GraphQL';
$modelClassDir = __DIR__ . '/GraphQL';

/** Describe graphql schema */
$schema = SchemaBuilder::build(__DIR__ . '/schema.graphql');
$namespace = 'Axtiva\FlexibleGraphql\Example\GraphQL';
$dir = __DIR__ . '/GraphQL';
$builder = new CodeGeneratorBuilder(new CodeGeneratorConfig($dir, CodeGeneratorConfig::V7_4, $namespace));

$generator = $builder->build();

/** @var ObjectType $type */
$type = $schema->getType('Query');
$field = $type->getField('account');
foreach($generator->generateFieldResolver($type, $field, $schema) as $item);
