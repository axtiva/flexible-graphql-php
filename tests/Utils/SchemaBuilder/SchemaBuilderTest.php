<?php

namespace Axtiva\FlexibleGraphql\Tests\Utils\SchemaBuilder;

use Axtiva\FlexibleGraphql\Utils\SchemaBuilder;
use GraphQL\Type\Definition\ObjectType;
use PHPUnit\Framework\TestCase;

class SchemaBuilderTest extends TestCase
{
    public function testCompositeTwoSchemas()
    {
        $glob = __DIR__ . '/resources/*.graphql';
        $schema = SchemaBuilder::build($glob);

        $this->assertNotFalse($schema->hasType('Query'), 'Query type not found');
        $this->assertNotFalse($schema->hasType('Account'), 'Account type not found');
        /** @var ObjectType $query */
        $query = $schema->getType('Query');
        $this->assertNotFalse($query->hasField('sum'), 'Query.sum field not found');
        $this->assertNotFalse($query->hasField('name'), 'Query.name field not found');
    }
}