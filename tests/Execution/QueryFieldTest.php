<?php

namespace Axtiva\FlexibleGraphql\Tests\Execution;

use Axtiva\FlexibleGraphql\Tests\Helper\ExampleSchemaSetupHelper;
use GraphQL\GraphQL;
use PHPUnit\Framework\TestCase;

class QueryFieldTest extends TestCase
{
    public function testDynamicSum()
    {
        $schema = ExampleSchemaSetupHelper::setup();

        $queryString = <<<'GRAPHQL'
query {
  dynamicSum(x: 1, y: 2)
}
GRAPHQL;

        $result = GraphQL::executeQuery(
            $schema,
            $queryString,
            $rootValue = null,
            $context = null,
            $variableValues = null,
            $operationName = null,
            $fieldResolver = null,
            $validationRules = null
        );

        $this->assertEquals(7, $result->data['dynamicSum']);
    }

    public function testRepresentation()
    {
        $schema = ExampleSchemaSetupHelper::setup();

        $queryString = <<<'GRAPHQL'
query {
    _entities(representations: [{__typename: "Account", id: "9999"}]) {
        ... on Account {
            id
        }
    }
}
GRAPHQL;

        $result = GraphQL::executeQuery(
            $schema,
            $queryString,
            $rootValue = null,
            $context = null,
            $variableValues = null,
            $operationName = null,
            $fieldResolver = null,
            $validationRules = null
        );

        $this->assertEquals('9999', $result->data['_entities'][0]['id']);
    }

}