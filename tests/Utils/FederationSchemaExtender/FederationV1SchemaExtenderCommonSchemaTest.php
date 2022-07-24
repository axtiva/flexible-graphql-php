<?php

namespace Axtiva\FlexibleGraphql\Tests\Utils\FederationSchemaExtender;

use Axtiva\FlexibleGraphql\Utils\FederationV1SchemaExtender;
use GraphQL\Error\SyntaxError;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Utils\BuildSchema;
use PHPUnit\Framework\TestCase;

class FederationV1SchemaExtenderCommonSchemaTest extends TestCase
{
    /**
     * @param string $sdl
     * @return void
     * @dataProvider dataProviderNotFederatedSchema
     * @throws SyntaxError
     */
    public function testNotExtendSchemaWithoutKeyDirectiveQuery(string $sdl)
    {
        $ast = Parser::parse($sdl);
        $schema = BuildSchema::build($ast);
        $schemaExtended = FederationV1SchemaExtender::build($schema, $ast);

        $this->assertFalse($schemaExtended->getQueryType()->hasField('_entities'), '_entity found');
        $this->assertTrue($schemaExtended->getQueryType()->hasField('_service'), '_service found');
        $this->assertNotNull($schemaExtended->getDirective('external'), 'external not found');
        $this->assertNotNull($schemaExtended->getDirective('requires'), 'requires not found');
        $this->assertNotNull($schemaExtended->getDirective('provides'), 'provides not found');
        $this->assertNotNull($schemaExtended->getDirective('extends'), 'extends not found');
        $this->assertNotNull($schemaExtended->getDirective('key'), 'key not found');

    }

    /**
     * @param string $sdl
     * @return void
     * @dataProvider dataProviderNotFederatedSchema
     * @throws SyntaxError
     */
    public function testNotExtendSchemaWithoutKeyDirective_Entity(string $sdl)
    {
        $ast = Parser::parse($sdl);
        $schema = BuildSchema::build($ast);
        $schemaExtended = FederationV1SchemaExtender::build($schema, $ast);

        $this->assertFalse($schemaExtended->hasType('_Entity'), '_Entity found');
    }

    /**
     * @param string $sdl
     * @return void
     * @dataProvider dataProviderNotFederatedSchema
     * @throws SyntaxError
     */
    public function testNotExtendSchemaWithoutKeyDirective_Any(string $sdl)
    {
        $ast = Parser::parse($sdl);
        $schema = BuildSchema::build($ast);
        $schemaExtended = FederationV1SchemaExtender::build($schema, $ast);

        $this->assertFalse($schemaExtended->hasType('_Any'), '_Any found');
    }

    /**
     * @param string $sdl
     * @return void
     * @dataProvider dataProviderNotFederatedSchema
     * @throws SyntaxError
     */
    public function testNotExtendSchemaWithoutKeyDirective_Service(string $sdl)
    {
        $ast = Parser::parse($sdl);
        $schema = BuildSchema::build($ast);
        $schemaExtended = FederationV1SchemaExtender::build($schema, $ast);

        $this->assertTrue($schemaExtended->hasType('_Service'), '_Service not found');
        $this->assertTrue($schemaExtended->hasType('Query'), 'Query not found');
        /** @var ObjectType $query */
        $query = $schemaExtended->getType('Query');
        $this->assertTrue((bool) $query->getField('_service'), '_service not found');
    }



    public function dataProviderNotFederatedSchema(): iterable
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
SDL];
    }
}