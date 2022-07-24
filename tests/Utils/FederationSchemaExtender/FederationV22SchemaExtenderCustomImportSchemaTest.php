<?php

namespace Axtiva\FlexibleGraphql\Tests\Utils\FederationSchemaExtender;

use Axtiva\FlexibleGraphql\Utils\FederationV1SchemaExtender;
use Axtiva\FlexibleGraphql\Utils\FederationV22SchemaExtender;
use GraphQL\Error\SyntaxError;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Utils\BuildSchema;
use PHPUnit\Framework\TestCase;

class FederationV22SchemaExtenderCustomImportSchemaTest extends TestCase
{
    /**
     * @return void
     * @throws SyntaxError
     */
    public function testForGlobalAliasAs()
    {
        $sdl = <<<'SDL'
scalar _FieldSet
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
directive @isAuthenticated on FIELD | FIELD_DEFINITION
directive @hasRole(role: String) on FIELD | FIELD_DEFINITION
directive @pow(ex: Int!) on FIELD | FIELD_DEFINITION
directive @uppercase on FIELD | FIELD_DEFINITION

extend schema @link(url: "http://localhost:8080/graphql", as: "@omg")

type Query {
  hero: Character
}

type Character {
  name: String
  friends: [Character]
  homeWorld: Planet
  species: Species
}

type Planet {
  name: String
  climate: String
}

type Species {
  name: String
  lifespan: Int
  origin: Planet
}
SDL;

        $ast = Parser::parse($sdl);
        $schema = BuildSchema::build($ast);
        $schemaExtended = FederationV22SchemaExtender::build($schema, $ast);

        $this->assertFalse($schemaExtended->getQueryType()->hasField('_entities'), '_entity found');
        $this->assertTrue($schemaExtended->getQueryType()->hasField('_service'), '_service found');
        $this->assertNotNull($schemaExtended->getDirective('external'), 'external not found');
        $this->assertNotNull($schemaExtended->getDirective('requires'), 'requires not found');
        $this->assertNotNull($schemaExtended->getDirective('provides'), 'provides not found');
        $this->assertNotNull($schemaExtended->getDirective('extends'), 'extends not found');
        $this->assertNotNull($schemaExtended->getDirective('link'), 'link not found');
        $this->assertNotNull($schemaExtended->getDirective('shareable'), 'shareable not found');
        $this->assertNotNull($schemaExtended->getDirective('omg__shareable'), 'omg__shareable not found');
        $this->assertNotNull($schemaExtended->getDirective('federation__shareable'), 'federation__shareable not found');
        $this->assertNotNull($schemaExtended->getDirective('inaccessible'), 'inaccessible not found');
        $this->assertNotNull($schemaExtended->getDirective('omg__inaccessible'), 'omg__inaccessible not found');
        $this->assertNotNull($schemaExtended->getDirective('federation__inaccessible'), 'federation__inaccessible not found');
        $this->assertNotNull($schemaExtended->getDirective('tag'), 'tag not found');
        $this->assertNotNull($schemaExtended->getDirective('omg__tag'), 'omg__tag not found');
        $this->assertNotNull($schemaExtended->getDirective('federation__tag'), 'federation__tag not found');
        $this->assertNotNull($schemaExtended->getDirective('override'), 'override not found');
        $this->assertNotNull($schemaExtended->getDirective('omg__override'), 'omg__override not found');
        $this->assertNotNull($schemaExtended->getDirective('federation__override'), 'federation__override not found');
    }

    /**
     * @return void
     * @throws SyntaxError
     */
    public function testForAliasImport()
    {
        $sdl = <<<'SDL'
scalar _FieldSet
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
directive @isAuthenticated on FIELD | FIELD_DEFINITION
directive @hasRole(role: String) on FIELD | FIELD_DEFINITION
directive @pow(ex: Int!) on FIELD | FIELD_DEFINITION
directive @uppercase on FIELD | FIELD_DEFINITION

extend schema @link(
url: "http://localhost:8080/graphql", 
import: [
        { name: "@shareable", as: "@omgs" }, 
        { name: "@inaccessible", as: "@iomg" }
    ]
)

type Query {
  hero: Character
}

type Character {
  name: String
  friends: [Character]
  homeWorld: Planet
  species: Species
}

type Planet {
  name: String
  climate: String
}

type Species {
  name: String
  lifespan: Int
  origin: Planet
}
SDL;

        $ast = Parser::parse($sdl);
        $schema = BuildSchema::build($ast);
        $schemaExtended = FederationV22SchemaExtender::build($schema, $ast);

        $this->assertFalse($schemaExtended->getQueryType()->hasField('_entities'), '_entity found');
        $this->assertTrue($schemaExtended->getQueryType()->hasField('_service'), '_service found');
        $this->assertNotNull($schemaExtended->getDirective('external'), 'external not found');
        $this->assertNotNull($schemaExtended->getDirective('requires'), 'requires not found');
        $this->assertNotNull($schemaExtended->getDirective('provides'), 'provides not found');
        $this->assertNotNull($schemaExtended->getDirective('extends'), 'extends not found');
        $this->assertNotNull($schemaExtended->getDirective('link'), 'link not found');
        $this->assertNotNull($schemaExtended->getDirective('shareable'), 'shareable not found');
        $this->assertNotNull($schemaExtended->getDirective('omgs'), 'omgs not found');
        $this->assertNotNull($schemaExtended->getDirective('federation__shareable'), 'federation__shareable not found');
        $this->assertNotNull($schemaExtended->getDirective('inaccessible'), 'inaccessible not found');
        $this->assertNotNull($schemaExtended->getDirective('iomg'), 'iomg not found');
        $this->assertNotNull($schemaExtended->getDirective('federation__inaccessible'), 'federation__inaccessible not found');
        $this->assertNotNull($schemaExtended->getDirective('tag'), 'tag not found');
        $this->assertNotNull($schemaExtended->getDirective('federation__tag'), 'federation__tag not found');
        $this->assertNotNull($schemaExtended->getDirective('override'), 'override not found');
        $this->assertNotNull($schemaExtended->getDirective('federation__override'), 'federation__override not found');
    }

}