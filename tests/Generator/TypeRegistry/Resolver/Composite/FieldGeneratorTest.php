<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Tests\Generator\TypeRegistry\Resolver\Composite;

use Axtiva\FlexibleGraphql\Generator\Exception\UnsupportedType;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\FieldResolverGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\Resolver\Composite\FieldGenerator;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Utils\BuildSchema;
use PHPUnit\Framework\TestCase;

final class FieldGeneratorTest extends TestCase
{
    public function testGenerateThrowsUnsupportedTypeWhenNoResolverMatches(): void
    {
        $schema = BuildSchema::build(Parser::parse(<<<GQL
type Query {
    hello: String
}
GQL));

        /** @var ObjectType $type */
        $type = $schema->getType('Query');
        $field = $type->getField('hello');

        $generator = new FieldGenerator(new class implements FieldResolverGeneratorInterface {
            public function hasResolver(\GraphQL\Type\Definition\Type $type, \GraphQL\Type\Definition\FieldDefinition $field): bool
            {
                return false;
            }

            public function generate(\GraphQL\Type\Definition\Type $type, \GraphQL\Type\Definition\FieldDefinition $field): string
            {
                return '';
            }
        });

        $this->expectException(UnsupportedType::class);
        $this->expectExceptionMessage('Unsupported type for generation: GraphQL\\Type\\Definition\\ObjectType.hello');

        $generator->generate($type, $field);
    }
}
