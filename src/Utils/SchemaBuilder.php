<?php

namespace Axtiva\FlexibleGraphql\Utils;

use Axtiva\FlexibleGraphql\Exception\SchemaParserException;
use GraphQL\Language\Parser;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use GraphQL\Utils\SchemaExtender;
use Throwable;

class SchemaBuilder
{
    public static function build(string $globTemplate): Schema
    {
        $files = glob($globTemplate);
        if (empty($files)) {
            throw new SchemaParserException('Schema files did not found in path: ' . $globTemplate);
        }

        try {
            $schema = null;
            foreach ($files as $fsElement) {
                if (is_file($fsElement)) {
                    if (empty($schema)) {
                        $schema = BuildSchema::build(Parser::parse(file_get_contents($fsElement)));
                    } else {
                        $schema = SchemaExtender::extend($schema, Parser::parse(file_get_contents($fsElement)));
                    }
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
}