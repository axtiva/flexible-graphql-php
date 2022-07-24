<?php

namespace Axtiva\FlexibleGraphql\Tests\Helper;

use Axtiva\FlexibleGraphql\Example\GraphQL\TypeRegistry;
use Axtiva\FlexibleGraphql\Example\PsrContainerExample;
use GraphQL\Type\Schema;

class ExampleSchemaSetupHelper
{
    public static function setup(): Schema
    {
        /**
         * Define services for resolving data
         */
        $container = new PsrContainerExample([
            \Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\Query\AccountResolver::class =>
                new \Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\Query\AccountResolver,
            \Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\Query\SumResolver::class =>
                new \Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\Query\SumResolver,
            \Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\Query\AddHourResolver::class =>
                new \Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\Query\AddHourResolver,
            \Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\Query\DynamicSumResolver::class =>
                new \Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\Query\DynamicSumResolver,
            // Service name are equal name defined at $fieldResolverMap in file example/generate_type_registry.php
            \Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\Account\TransactionsResolver::class =>
                new \Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\Account\TransactionsResolver,
            \Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\Transaction\StatusResolver::class =>
                new \Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\Transaction\StatusResolver,
            // Service name are equal name defined at $unionTypeResolverMap in file example/generate_type_registry.php
            \Axtiva\FlexibleGraphql\Example\GraphQL\UnionResolveType\CurrencyTypeResolver::class =>
                new \Axtiva\FlexibleGraphql\Example\GraphQL\UnionResolveType\CurrencyTypeResolver,
            // Service name are equal name defined at $scalarResolverMap in file example/generate_type_registry.php
            \Axtiva\FlexibleGraphql\Example\GraphQL\Scalar\DateTimeScalar::class => new \Axtiva\FlexibleGraphql\Example\GraphQL\Scalar\DateTimeScalar,
            // Service name are equal name defined at $directiveResolverMap in file example/generate_type_registry.php
            \Axtiva\FlexibleGraphql\Example\GraphQL\Directive\UppercaseDirective::class => new \Axtiva\FlexibleGraphql\Example\GraphQL\Directive\UppercaseDirective,
            \Axtiva\FlexibleGraphql\Example\GraphQL\Directive\PlusXDirective::class => new \Axtiva\FlexibleGraphql\Example\GraphQL\Directive\PlusXDirective,
            \Axtiva\FlexibleGraphql\Example\GraphQL\Representation\AccountRepresentation::class => new \Axtiva\FlexibleGraphql\Example\GraphQL\Representation\AccountRepresentation,
            \Axtiva\FlexibleGraphql\Example\GraphQL\Representation\TransactionRepresentation::class => new \Axtiva\FlexibleGraphql\Example\GraphQL\Representation\TransactionRepresentation,
            \Axtiva\FlexibleGraphql\Example\GraphQL\UnionResolveType\_EntityTypeResolver::class => new \Axtiva\FlexibleGraphql\Example\GraphQL\UnionResolveType\_EntityTypeResolver,

            \Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\Query\_EntitiesResolver::class
            => new \Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\Query\_EntitiesResolver(...[
                'Account' => new \Axtiva\FlexibleGraphql\Example\GraphQL\Representation\AccountRepresentation(),
                'Transaction' => new \Axtiva\FlexibleGraphql\Example\GraphQL\Representation\TransactionRepresentation(),
            ]),
            \Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\Query\_ServiceResolver::class => new \Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\Query\_ServiceResolver(
                file_get_contents(__DIR__ . '/../../example/schema.graphql')
            ),
        ]);

        /**
         * Create schema from generated TypeRegistry
         */

        $typeRegistry = new TypeRegistry($container);

        return new Schema([
            'query' => $typeRegistry->getType('Query'),
            'mutation' => $typeRegistry->getType('Mutation'),
            'typeLoader' => static function(string $name) use ($typeRegistry) : \GraphQL\Type\Definition\Type {
                return $typeRegistry->getType($name);
            },
        ]);
    }
}