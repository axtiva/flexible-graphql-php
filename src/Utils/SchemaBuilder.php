<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Utils;

use Axtiva\FlexibleGraphql\Exception\SchemaParserException;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use GraphQL\Utils\SchemaExtender;
use Throwable;

class SchemaBuilder
{
    public static function build(string $globTemplate): Schema
    {
        try {
            $schema = null;
            foreach (self::getSchemaAst($globTemplate) as $ast) {
                if (empty($schema)) {
                    $schema = BuildSchema::build($ast);
                } else {
                    $schema = SchemaExtender::extend($schema, $ast);
                }
            }
        } catch (Throwable $e) {
            throw new SchemaParserException($e->getMessage(), $e->getCode(), $e);
        }

        if (empty($schema)) {
            throw new SchemaParserException('Schema files did not found in path: ' . $globTemplate);
        }

        return $schema;
    }

    /**
     * @param string $globTemplate
     * @return DocumentNode[]
     * @throws SchemaParserException
     */
    public static function getSchemaAst(string $globTemplate): iterable
    {
        $files = glob($globTemplate);
        if (empty($files)) {
            throw new SchemaParserException('Schema files did not found in path: ' . $globTemplate);
        }

        try {
            foreach ($files as $fsElement) {
                if (is_file($fsElement)) {
                    yield Parser::parse(file_get_contents($fsElement));
                }
            }
        } catch (Throwable $e) {
            throw new SchemaParserException($e->getMessage(), $e->getCode(), $e);
        }
    }
}