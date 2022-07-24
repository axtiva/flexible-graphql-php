# Axtiva Flexible Graphql

Schema first lib for generate php code from graphql sdl to TypeRegistry whom support [webonyx/graphql-php](https://github.com/webonyx/graphql-php) with simple integrate to any controller.

## TL;DR

Can easy to integrate graphql to any project, all you need is controller. Fast start with [example](./example/TEST.md)

## Features:

- Schema/SDL first code generation (look at [example dir](./example/generate_code.php))
- Support all features from [webonyx/graphql-php](https://github.com/webonyx/graphql-php)
- Executable directives
- Apollo Federation/Federation2 support
- Popular framework integration:
  + Symfony [axtiva/flexible-graphql-bundle](//github.com/axtiva/flexible-graphql-bundle)

# Setup

Composer install:

```
composer require axtiva/flexible-graphql-php
```

## Examples:

- Generate models by types in Graphql Schema [example/generate_type_models.php](./example/generate_type_models.php)
- Generate custom scalar resolver by Graphql Schema [example/generate_scalar_resolver.php](./example/generate_scalar_resolver.php)
- Generate directive resolver by Graphql Schema [example/generate_directive_resolver.php](./example/generate_directive_resolver.php)
- Generate type field resolver by Graphql Schema [example/generate_field_resolver.php](./example/generate_field_resolver.php)
- Create lazy loaded TypeRegistry [example/generate_type_registry.php](./example/generate_type_registry.php)
- Setup graphql request handler with lazy loaded TypeRegistry [example/start_graphql_server.php](./example/start_graphql_server.php)

## Demo

Up Dev server for test http Graphql requests:

```shell
php -S localhost:8080 example/start_graphql_server.php
```

### Change [schema](example/schema.graphql) and run example commands
 
Update TypeRegistry and model classes from schema and resolvers map:

```shell
php example/generate_code.php
```

If you need to make field resolver, then remove AutoGenerationInterface from model [CodedCurrencyType](example/GraphQL/Model/CodedCurrencyType.php)

Example:

```diff
- final class CodedCurrencyType implements AutoGenerationInterface, NodeInterface, CurrencyInterface
+ final class CodedCurrencyType implements NodeInterface, CurrencyInterface
{
    public string $id;
-   public int $code;
}
```

and run `php example/generate_code.php`, after this you will find in [example/Resolver/CodedCurrency/CodeResolver.php](example/Resolver/CodedCurrency/CodeResolver.php).
this is your field resolver, define him in your psr container like PsrContainerExample in [example/start_graphql_server.php](example/start_graphql_server.php):

```diff
$container = new PsrContainerExample([
    \Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\Query\AccountResolver::class =>
        new \Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\Query\AccountResolver,
+   \Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\CodedCurrency\CodeResolver::class =>
+       new \Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\CodedCurrency\CodeResolver,
]);
```

Run demo app `php -S localhost:8080 example/start_graphql_server.php` and try request CodedCurrency.code field in query 

## Tests

Run tests

```
php vendor/bin/phpunit 
```