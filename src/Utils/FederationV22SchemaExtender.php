<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Utils;

use GraphQL\Language\AST\ArgumentNode;
use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\ListValueNode;
use GraphQL\Language\AST\ObjectValueNode;
use GraphQL\Language\AST\ObjectFieldNode;
use GraphQL\Language\AST\SchemaExtensionNode;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Language\AST\ValueNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Schema;
use GraphQL\Utils\SchemaExtender;

class FederationV22SchemaExtender extends FederationV1SchemaExtender
{
    private const SHAREABLE = 'directive @%s on OBJECT | FIELD_DEFINITION';
    private const INACCESSIBLE = 'directive @%s on FIELD_DEFINITION | OBJECT | INTERFACE | UNION | ARGUMENT_DEFINITION | SCALAR | ENUM | ENUM_VALUE | INPUT_OBJECT | INPUT_FIELD_DEFINITION';
    private const OVERRIDE = 'directive @%s(from: String!) on FIELD_DEFINITION';
    private const TAG = 'directive @%s(name: String!) repeatable on | FIELD_DEFINITION | INTERFACE | OBJECT | UNION | ARGUMENT_DEFINITION | SCALAR | ENUM | ENUM_VALUE | INPUT_OBJECT | INPUT_FIELD_DEFINITION';
    private const EXTERNAL = 'directive @%s on FIELD_DEFINITION';
    private const REQUIRES = 'directive @%s(fields: FieldSet!) on FIELD_DEFINITION';
    private const PROVIDES = 'directive @%s(fields: FieldSet!) on FIELD_DEFINITION';
    private const KEY = 'directive @%s(fields: FieldSet!, resolvable: Boolean = true) repeatable on OBJECT | INTERFACE';
    private const EXTENDS = 'directive @%s on OBJECT | INTERFACE';

    private const DIRECTIVE_MAP = [
        'tag' => self::TAG,
        'shareable' => self::SHAREABLE,
        'inaccessible' => self::INACCESSIBLE,
        'override' => self::OVERRIDE,
        'external' => self::EXTERNAL,
        'requires' => self::REQUIRES,
        'provides' => self::PROVIDES,
        'key' => self::KEY,
        'extends' => self::EXTENDS,
    ];

    public static function build(Schema $schema, DocumentNode $ast): Schema
    {
        $schema = parent::build($schema, $ast);

        $schema = self::addDirectiveIfNotExists(
            $schema,
            'link',
            <<<GQL
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
GQL
        );

        $schema = self::addDirectiveIfNotExists($schema, 'shareable', sprintf(self::SHAREABLE, 'shareable'));
        $schema = self::addDirectiveIfNotExists($schema, 'inaccessible', sprintf(self::INACCESSIBLE, 'inaccessible'));
        $schema = self::addDirectiveIfNotExists($schema, 'override', sprintf(self::OVERRIDE, 'override'));
        $schema = self::addDirectiveIfNotExists($schema, 'tag', sprintf(self::TAG, 'tag'));
        $schema = self::addFederationDirectivesWithAliases($schema, 'federation');

        $hasLinkExtension = false;
        foreach ($ast->definitions as $definition) {
            if (!$definition instanceof SchemaExtensionNode) {
                continue;
            }

            /** @var DirectiveNode $directive */
            foreach ($definition->directives as $directive) {
                if ($directive->name->value !== 'link') {
                    continue;
                }

                $hasLinkExtension = true;

                /** @var ArgumentNode $argument */
                foreach ($directive->arguments as $argument) {
                    if ($argument->name->value === 'as') {
                        $alias = self::extractStringValue($argument->value);
                        if ($alias !== null) {
                            $schema = self::addFederationDirectivesWithAliases(
                                $schema,
                                self::trimDirectivePrefix($alias),
                            );
                        }
                    } elseif ($argument->name->value === 'import' && $argument->value instanceof ListValueNode) {
                        foreach ($argument->value->values as $value) {
                            if ($value instanceof StringValueNode) {
                                $directiveName = self::trimDirectivePrefix($value->value);
                                if (empty(self::DIRECTIVE_MAP[$directiveName])) {
                                    continue;
                                }
                                $schema = self::addDirectiveIfNotExists(
                                    $schema,
                                    $directiveName,
                                    sprintf(self::DIRECTIVE_MAP[$directiveName], $directiveName)
                                );
                            } elseif ($value instanceof ObjectValueNode) {
                                $name = null;
                                $as = null;
                                /** @var ObjectFieldNode $field */
                                foreach ($value->fields as $field) {
                                    if ($field->name->value === 'name') {
                                        $nameValue = self::extractStringValue($field->value);
                                        if ($nameValue !== null) {
                                            $name = self::trimDirectivePrefix($nameValue);
                                        }
                                    }
                                    if ($field->name->value === 'as') {
                                        $aliasValue = self::extractStringValue($field->value);
                                        if ($aliasValue !== null) {
                                            $as = self::trimDirectivePrefix($aliasValue);
                                        }
                                    }
                                }

                                if ($name === null || empty(self::DIRECTIVE_MAP[$name])) {
                                    continue;
                                }

                                if ($as !== null) {
                                    $schema = self::addDirectiveIfNotExists(
                                        $schema,
                                        $as,
                                        sprintf(self::DIRECTIVE_MAP[$name], $as)
                                    );
                                } else {
                                    $schema = self::addDirectiveIfNotExists(
                                        $schema,
                                        $name,
                                        sprintf(self::DIRECTIVE_MAP[$name], $name)
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!$hasLinkExtension) {
            $documentAST = Parser::parse(
                'extend schema @link(url: "https://specs.apollo.dev/federation/v2.2")'
            );
            $schema = SchemaExtender::extend($schema, $documentAST);
        }

        return $schema;
    }

    private static function addFederationDirectivesWithAliases(Schema $schema, string $alias): Schema
    {
        foreach (self::DIRECTIVE_MAP as $directiveName => $directive) {
            $schema = self::addDirectiveIfNotExists(
                $schema,
                $alias . '__' . $directiveName,
                sprintf($directive, $alias . '__' . $directiveName)
            );
        }

        return $schema;
    }

    private static function trimDirectivePrefix(string $value): string
    {
        return ltrim($value, '@');
    }

    private static function extractStringValue(ValueNode $value): ?string
    {
        if ($value instanceof StringValueNode) {
            return $value->value;
        }

        return null;
    }
}
