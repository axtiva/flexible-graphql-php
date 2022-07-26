<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Utils;

use GraphQL\Language\Parser;
use GraphQL\Type\Schema;
use GraphQL\Utils\SchemaExtender;

class FederationV2SchemaExtender extends FederationV1SchemaExtender
{
    public static function build(Schema $schema): Schema
    {
        $schema = parent::build($schema);
        $schema = self::addDirectiveIfNotExists($schema, 'shareable', 'directive @shareable on OBJECT | FIELD_DEFINITION');
        $schema = self::addDirectiveIfNotExists($schema, 'inaccessible', 'directive @inaccessible on FIELD_DEFINITION | OBJECT | INTERFACE | UNION | ARGUMENT_DEFINITION | SCALAR | ENUM | ENUM_VALUE | INPUT_OBJECT | INPUT_FIELD_DEFINITION');
        $schema = self::addDirectiveIfNotExists($schema, 'override', 'directive @override(from: String!) on FIELD_DEFINITION');
        $schema = self::addDirectiveIfNotExists($schema, 'tag', 'directive @tag(name: String!) repeatable on | FIELD_DEFINITION | INTERFACE | OBJECT | UNION | ARGUMENT_DEFINITION | SCALAR | ENUM | ENUM_VALUE | INPUT_OBJECT | INPUT_FIELD_DEFINITION');
        $schema = self::addDirectiveIfNotExists($schema, 'link', <<<GQL
directive @link(
  url: String, 
  as: String, 
  for: link__Purpose, 
  import: [link__Import]
) repeatable on SCHEMA
enum link__Purpose {
    "`SECURITY` features provide metadata necessary to securely resolve fields."
    SECURITY
    "`EXECUTION` features provide metadata necessary for operation execution."
    EXECUTION
}
scalar link__Import
GQL);

        $documentAST = Parser::parse('extend schema @link(url: "https://specs.apollo.dev/federation/v2.0")');
        return SchemaExtender::extend($schema, $documentAST);
    }
}