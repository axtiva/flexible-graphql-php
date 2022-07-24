<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Utils;

use Axtiva\FlexibleGraphql\Resolver\Foundation\_ServiceResolver;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\SchemaDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Language\Printer;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use GraphQL\Utils\SchemaExtender;
use function in_array;

class FederationV1SchemaExtender
{
    public static function build(Schema $schema, DocumentNode $ast): Schema
    {
        $schema = self::addTypeIfNotExists($schema, '_FieldSet', 'scalar _FieldSet');
        $schema = self::addTypeIfNotExists($schema, 'FieldSet', 'scalar FieldSet');

        $schema = self::addDirectiveIfNotExists($schema, 'external', 'directive @external on FIELD_DEFINITION');
        $schema = self::addDirectiveIfNotExists($schema, 'requires', 'directive @requires(fields: FieldSet!) on FIELD_DEFINITION');
        $schema = self::addDirectiveIfNotExists($schema, 'provides', 'directive @provides(fields: FieldSet!) on FIELD_DEFINITION');
        $schema = self::addDirectiveIfNotExists($schema, 'key', 'directive @key(fields: FieldSet!, resolvable: Boolean = true) repeatable on OBJECT | INTERFACE');
        $schema = self::addDirectiveIfNotExists($schema, 'extends', 'directive @extends on OBJECT | INTERFACE');

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

        if ($schema->astNode === null) { // generate ast node for _service.sdl field
            $schema = self::extendSchemaAst($schema);
        }

        /** @var ObjectType $query */
        $query = $schema->getType('Query');
        if ($query->getField('_service')->resolveFn === null) {
            $query->getField('_service')->resolveFn = new _ServiceResolver(Printer::doPrint($schema->astNode));
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

    protected static function addDirectiveIfNotExists(Schema $schema, string $directiveName, string $definition): Schema
    {
        if ($schema->getDirective($directiveName)) {
            return $schema;
        }

        $documentAST = Parser::parse($definition);
        return SchemaExtender::extend($schema, $documentAST);
    }

    protected static function addTypeIfNotExists(Schema $schema, string $typeName, string $definition): Schema
    {
        if ($schema->getType($typeName)) {
            return $schema;
        }

        $documentAST = Parser::parse($definition);
        return SchemaExtender::extend($schema, $documentAST);
    }

    private static function extendSchemaAst(Schema $schema): Schema
    {
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
        $schema->astNode = $schemaDefinition;
        return $schema;
    }
}