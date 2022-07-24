<?php

namespace Axtiva\FlexibleGraphql\Tests\Utils\FederationSchemaExtender;

use Axtiva\FlexibleGraphql\Utils\FederationV1SchemaExtender;
use GraphQL\Error\SyntaxError;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\UnionType;
use GraphQL\Utils\BuildSchema;
use PHPUnit\Framework\TestCase;

class FederationV1SchemaExtenderFederationSchemaTest extends TestCase
{
    /**
     * @param string $sdl
     * @return void
     * @dataProvider dataProviderFederatedSchema
     * @throws SyntaxError
     */
    public function testExtendSchema(string $sdl)
    {
        $ast = Parser::parse($sdl);
        $schema = BuildSchema::build($ast);
        $schemaExtended = FederationV1SchemaExtender::build($schema, $ast);

        $this->assertTrue($schemaExtended->getQueryType()->hasField('_entities'), '_entity not found');
        $this->assertTrue($schemaExtended->getQueryType()->hasField('_service'), '_service not found');
        $this->assertTrue($schemaExtended->hasType('_Entity'), '_Entity not found');
        $this->assertTrue($schemaExtended->hasType('_Any'), '_Any not found');
        $this->assertTrue($schemaExtended->hasType('_Service'), '_Service not found');
    }

    /**
     * @param string $sdl
     * @return void
     * @dataProvider dataProviderFederatedSchema
     * @throws SyntaxError
     */
    public function testFederation_Entity(string $sdl)
    {
        $ast = Parser::parse($sdl);
        $schema = BuildSchema::build($ast);
        $schemaExtended = FederationV1SchemaExtender::build($schema, $ast);

        /** @var UnionType $_Entity */
        $_Entity = $schemaExtended->getType('_Entity');
        $this->assertTrue($_Entity instanceof UnionType);

        foreach ($_Entity->getTypes() as $wrappedType) {
            $this->assertContains(
                $wrappedType->name,
                [
                    'Character',
                    'Species',
                ]
            );
            $this->assertNotContains(
                $wrappedType->name,
                [
                    'Planet',
                    'Query',
                    'Date',
                    'Episode',
                ]
            );
        }
    }


    public function testExtendSchemaWithoutKeyDirectiveQuery()
    {
        $sdl = <<<'SDL'
scalar FieldSet
directive @isAuthenticated on FIELD | FIELD_DEFINITION
directive @hasRole(role: String) on FIELD | FIELD_DEFINITION
directive @pow(ex: Int!) on FIELD | FIELD_DEFINITION
directive @uppercase on FIELD | FIELD_DEFINITION
directive @key(fields: FieldSet!, resolvable: Boolean = true) repeatable on OBJECT | INTERFACE
directive @extends on OBJECT | INTERFACE

type Query {
  hero: Character
}

type Character @key(fields: "id") {
  id: ID!
  name: String
  friends: [Character]
  homeWorld: Planet
  species: Species
}

type Planet {
  name: String
  climate: String
}

type Species @key(fields: "id") {
  id: ID!
  name: String
  lifespan: Int
  origin: Planet
}

enum Episode {
  NEWHOPE
  EMPIRE
  JEDI
}

scalar Date
SDL;
        $ast = Parser::parse($sdl);
        $schema = BuildSchema::build($ast);
        $schemaExtended = FederationV1SchemaExtender::build($schema, $ast);

        $this->assertTrue($schemaExtended->getQueryType()->hasField('_entities'), '_entity not found');
        $this->assertTrue($schemaExtended->getQueryType()->hasField('_service'), '_service not found');
        $this->assertTrue($schemaExtended->hasType('_Entity'), '_Entity not found');
        $this->assertTrue($schemaExtended->hasType('_Any'), '_Any not found');
        $this->assertTrue($schemaExtended->hasType('_Service'), '_Service not found');
        $this->assertNotNull($schemaExtended->getDirective('external'), 'external not found');
        $this->assertNotNull($schemaExtended->getDirective('requires'), 'requires not found');
        $this->assertNotNull($schemaExtended->getDirective('provides'), 'provides not found');
        $this->assertNotNull($schemaExtended->getDirective('extends'), 'extends not found');
        $this->assertNotNull($schemaExtended->getDirective('key'), 'key not found');

    }

    public function dataProviderFederatedSchema(): iterable
    {
        yield [<<<'SDL'
scalar _FieldSet
directive @external on OBJECT | FIELD_DEFINITION
directive @requires(fields: _FieldSet!) on FIELD_DEFINITION
directive @provides(fields: _FieldSet!) on FIELD_DEFINITION
directive @key(fields: _FieldSet!) on OBJECT | INTERFACE
directive @extends on OBJECT | INTERFACE
directive @isAuthenticated on FIELD | FIELD_DEFINITION
directive @hasRole(role: String) on FIELD | FIELD_DEFINITION
directive @pow(ex: Int!) on FIELD | FIELD_DEFINITION
directive @uppercase on FIELD | FIELD_DEFINITION

type Query {
  hero: Character
}

type Character @key(fields: "id") {
  id: ID!
  name: String
  friends: [Character]
  homeWorld: Planet
  species: Species
}

type Planet {
  name: String
  climate: String
}

type Species @key(fields: "id") {
  id: ID!
  name: String
  lifespan: Int
  origin: Planet
}

enum Episode {
  NEWHOPE
  EMPIRE
  JEDI
}

scalar Date
SDL];
    }
}