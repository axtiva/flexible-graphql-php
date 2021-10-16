<?php

require(__DIR__ . '/../vendor/autoload.php');

/**
 * Setup webonix/graphql-php server
 */

use Axtiva\FlexibleGraphql\Example\GraphQL\TypeRegistry;
use Axtiva\FlexibleGraphql\Example\PsrContainerExample;
use Axtiva\FlexibleGraphql\Resolver\ResolverInterface;
use GraphQL\Error\DebugFlag;
use GraphQL\Server\ServerConfig;
use GraphQL\Server\StandardServer;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Schema;

/**
 * Define services for resolving data
 */

$container = new PsrContainerExample([
    \Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\Query\AccountResolver::class =>
        new \Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\Query\AccountResolver,
    \Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\Query\SumResolver::class =>
        new \Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\Query\SumResolver,
    // Service name are equal name defined at $fieldResolverMap in file example/generate_type_registry.php
    \Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\Account\TransactionsResolver::class =>
        new \Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\Account\TransactionsResolver,
    \Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\Transaction\StatusResolver::class =>
        new \Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\Transaction\StatusResolver,
    // Service name are equal name defined at $unionTypeResolverMap in file example/generate_type_registry.php
    \Axtiva\FlexibleGraphql\Example\GraphQL\UnionResolveType\CurrencyTypeResolver::class =>
        new Axtiva\FlexibleGraphql\Example\GraphQL\UnionResolveType\CurrencyTypeResolver,
    // Service name are equal name defined at $scalarResolverMap in file example/generate_type_registry.php
    \Axtiva\FlexibleGraphql\Example\GraphQL\Scalar\DateTimeScalar::class => new \Axtiva\FlexibleGraphql\Example\GraphQL\Scalar\DateTimeScalar,
    // Service name are equal name defined at $directiveResolverMap in file example/generate_type_registry.php
    \Axtiva\FlexibleGraphql\Example\GraphQL\Directive\UppercaseDirective::class => new \Axtiva\FlexibleGraphql\Example\GraphQL\Directive\UppercaseDirective,

]);

/**
 * Create schema from generated TypeRegistry
 */

$typeRegistry = new TypeRegistry($container);

$schema = new Schema([
    'query' => $typeRegistry->getType('Query'),
    'typeLoader' => static function(string $name) use ($typeRegistry) : GraphQL\Type\Definition\Type {
        return $typeRegistry->getType($name);
    },
]);

$debugFlag = DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE | DebugFlag::RETHROW_INTERNAL_EXCEPTIONS | DebugFlag::RETHROW_UNSAFE_EXCEPTIONS;
$config = ServerConfig::create()
    ->setSchema($schema)
    ->setDebugFlag($debugFlag)
;
$server = new StandardServer($config);
$server->handleRequest();