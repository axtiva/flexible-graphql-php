## Quick start

Start GraphQL server

```shell
php -S localhost:8080 example/start_graphql_server.php
```

### Fetch dynamicSum

```graphql
query {
  dynamicSum(x: 1, y: 2)
}
```

```shell
curl --request POST -sL \
     --url 'http://localhost:8080'\
     --header 'Content-Type: application/json' \
     --data-raw '{"query":"query {\n  dynamicSum(x: 1, y: 2)\n}"}'
```

What is happening here?

Code execute dynamicSum resolver with arguments x: 1, y: 2 based in [DynamicSumResolver class](./GraphQL/Resolver/Query/DynamicSumResolver.php)
and execute plusX directive with arguments x: 4 based in [PlusXDirective class](./GraphQL/Directive/PlusXDirective.php)
defined in [schema.graphql](./schema.graphql#L10) file in 10th line.

### Fetch Federation entities

```graphql
query {
    _entities(representations: [{__typename: "Account", id: "9999"}]) {
        ... on Account {
            id
        }
    }
}
```

```shell
curl --request POST -sL \
     --url 'http://localhost:8080'\
     --header 'Content-Type: application/json' \
     --data-raw '{"query":"query {\n    _entities(representations: [{__typename: \"Account\", id: \"9999\"}]) {\n        ... on Account {\n            id\n        }\n    }\n}"}'
```

What is happening here?

Code execute _entities resolver with arguments representations: [{__typename: "Account", id: "9999"}] based in 
[AccountRepresentation class](./GraphQL/Representation/AccountRepresentation.php)