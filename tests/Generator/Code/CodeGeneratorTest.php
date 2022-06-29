<?php

namespace Axtiva\FlexibleGraphql\Tests\Generator\Code;

use Axtiva\FlexibleGraphql\Builder\Foundation\CodeGeneratorBuilder;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\CodeGeneratorConfig;
use Axtiva\FlexibleGraphql\Tests\Helper\FileSystemHelper;
use GraphQL\Language\Parser;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use PHPUnit\Framework\TestCase;

class CodeGeneratorTest extends TestCase
{
    /**
     * @return void
     * @dataProvider dataProviderGeneratePhpCode
     */
    public function testQuantityOfTypes(int $totalTypes, Schema $schema)
    {
        $namespace = 'Axtiva\FlexibleGraphql\Example\GraphQL';
        $dir = uniqid('/tmp/TmpTestData/GraphQL');

        FileSystemHelper::rmdir($dir);
        FileSystemHelper::mkdir($dir);

        $builder = new CodeGeneratorBuilder(new CodeGeneratorConfig($dir, CodeGeneratorConfig::V7_4, $namespace));

        $generator = $builder->build();

        $i = 0;
        foreach ($generator->generateAllTypes($schema) as $type) {
            $i++;
        }
        $this->assertEquals($totalTypes, $i);
        FileSystemHelper::rmdir($dir);
    }

    public function testEmptyDirrectory()
    {
        $namespace = 'Axtiva\FlexibleGraphql\Example\GraphQL';
        $dir = '/tmp/TmpTestData/GraphQL';

        FileSystemHelper::rmdir($dir);

        $this->expectExceptionMessage('Directory for models does not exist ' . $dir . ' create it manually.');
        new CodeGeneratorBuilder(new CodeGeneratorConfig($dir, CodeGeneratorConfig::V7_4, $namespace));
    }

    public function dataProviderGeneratePhpCode(): iterable
    {
        yield [
            4, // NamedCurrency Node Query.number Query.numberArgs
            BuildSchema::build(Parser::parse(<<<GQL
"CAPITALIZE ALL LETTERS IN STRING"
directive @uppercase on FIELD | FIELD_DEFINITION
directive @plusX(x: Int!) on FIELD | FIELD_DEFINITION

type NamedCurrency implements Node {
    id: ID!
}

interface Node {
    id: ID!
}

type Query {
    number(test: Int): String!
}
GQL)),
        ];
    }
}