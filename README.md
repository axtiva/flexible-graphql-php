# Axtiva Flexible Graphql

Schema first lib for generate php code from graphql sdl to TypeRegistry whom support webonyx/graphql-php

## Features:

- Schema/SDL first code generation (look at [example dir](./example/generate_type_registry.php))
- Support all features from webonyx/graphql-php
- Executable directives
- Apollo Federation support by use [axtiva/graphql-federation-extension](//github.com/axtiva/graphql-federation-extension)
- Apollo Federation code generation by use [axtiva/flexible-graphql-federation](//github.com/axtiva/flexible-graphql-federation)
- Popular framework integration 
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
- Setup graphql request handler with lazy loaded TypeRegistry [example/setup_graphql_server.php](./example/setup_graphql_server.php)

## Demo

Up Dev server for test http Graphql requests:

```shell
php -S localhost:8080 example/setup_graphql_server.php
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
this is your field resolver, define him in your psr container like PsrContainerExample in [example/setup_graphql_server.php](example/setup_graphql_server.php):

```diff
$container = new PsrContainerExample([
    \Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\Query\AccountResolver::class =>
        new \Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\Query\AccountResolver,
+   \Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\CodedCurrency\CodeResolver::class =>
+       new \Axtiva\FlexibleGraphql\Example\GraphQL\Resolver\CodedCurrency\CodeResolver,
]);
```

Run demo app `php -S localhost:8080 example/setup_graphql_server.php` and try request CodedCurrency.code field in query 

## Tests

Run tests

```
php vendor/bin/phpunit 
```