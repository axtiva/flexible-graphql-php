2.1.1

- fix type registry generator for amphp lib

2.1.0

- add support of amphp v3 as [TypeRegistryGeneratorBuilderAmphp.php](src/Builder/Foundation/Psr/Container/TypeRegistryGeneratorBuilderAmphp.php)
- add support of amphp v2 as [TypeRegistryGeneratorBuilderAmphpV2.php](src/Builder/Foundation/Psr/Container/TypeRegistryGeneratorBuilderAmphpV2.php)

2.0.0

- Integrate federation directives and types without define it into sdl auto mount into main sdl
- Support features of apollo federation v1 and v2
- Upgrade webonyx/graphql-php to 15.x

1.1.1

- hotfix one level iterator

1.1.0

- make config generator friendly for users
- remove twig dependency
- adopt tests for parallel run
- add type hinting for iterable properties in InputTypes

1.0.4

- bugfix remove default scalar resolver from generators

1.0.3

- bugfix for empty properties in InputType and Args objects

1.0.2

- fix input type model generator
- generate empty main types if there are not defined in schema

1.0.1

- fix args decorator for scalar types

1.0.0 

Make user-friendly types and resolvers for generated classes

- add templates for different php version
- add some unit tests for generators
- add args fields generation
- add args directives generation
- add input type models
- typed custom scalar
- add php doc generation with typed arguments for resolvers

0.2.0

- add lazy load resolvers