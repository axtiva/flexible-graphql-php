<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Utils;

use Axtiva\FlexibleGraphql\Resolver\Foundation\_ServiceResolver;
use GraphQL\Language\AST\SchemaDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Language\Printer;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use GraphQL\Utils\SchemaExtender;
use function in_array;

final class FederationV1SchemaExtender
{
    public static function build(Schema $schema): Schema
    {
        $entityTypeNamesList = [];
        foreach ($schema->getTypeMap() as $type) {
            if ($type instanceof ObjectType && $type->astNode !== null) {
                foreach ($type->astNode->directives as $node) {
                    if ($node->name->kind === 'Name' && $node->name->value === 'key') {
                        $entityTypeNamesList[] = $type->name;
                        break;
                    }
                }
            }
        }

        $queryDefinition = in_array('Query', array_keys($schema->getTypeMap()))
            ? 'extend type Query @extends'
            : 'type Query @extends';

        $sdl = <<<GRAPHQL
type _Service {
  sdl: String!
}
$queryDefinition {
  _service: _Service!
}
GRAPHQL;

        $documentAST = Parser::parse($sdl);
        $schema = SchemaExtender::extend($schema, $documentAST);

        if ($schema->getAstNode() === null) { // generate ast node for _service.sdl field
            $schemaSDL = 'schema { query: Query }';
            $documentAST = Parser::parse($schemaSDL);
            /** @var SchemaDefinitionNode $schemaDefinition */
            $schemaDefinition = $documentAST->definitions[0];
            foreach ($schema->getDirectives() as $directive) {
                if ($directive->astNode) {
                    $schemaDefinition->directives[] = $directive->astNode;
                }
            }
            foreach (['query', 'mutation', 'subscription'] as $operation) {
                $operation = $schema->getOperationType($operation);
                if ($operation && $operation->astNode) {
                    $schemaDefinition->operationTypes[] = $operation->astNode;
                }
            }
            $schema->getConfig()->setAstNode($schemaDefinition);
        }

        /** @var ObjectType $query */
        $query = $schema->getType('Query');
        if ($query->getField('_service')->resolveFn === null) {
            $query->getField('_service')->resolveFn = new _ServiceResolver(Printer::doPrint($schema->getAstNode()));
        }

        if (empty($entityTypeNamesList)) {
            return $schema;
        }

        $entityTypeNames = implode(' | ', $entityTypeNamesList);
        $queryDefinition = 'extend type Query @extends';
        $unionEntitiesType = "union _Entity = $entityTypeNames";
        $sdl = <<< GRAPHQL

$unionEntitiesType
scalar _Any
$queryDefinition {
  _entities(representations: [_Any!]!): [_Entity]!
}
GRAPHQL;

        $documentAST = Parser::parse($sdl);
        return SchemaExtender::extend($schema, $documentAST);
    }
}