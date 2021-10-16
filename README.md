# Αχτινα Flexible Graphql

Schema first lib for generate php code from graphql sdl to TypeRegistry whom support webonyx/graphql-php

## Features:

- Schema/SDL first code generation (look at [example dir](./example/generate_type_registry.php))
- Support all features from webonyx/graphql-php
- Executable directives
- Apollo Federation support by use [axtiva/flexible-graphql-federation-extension](//github.com/axtiva/flexible-graphql-federation-extension)
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
 
Update TypeRegistry from schema and resolvers map:

```shell
php example/generate_type_registry.php
```
