<?php

namespace Axtiva\FlexibleGraphql\Tests\Builder;

use Axtiva\FlexibleGraphql\Builder\Foundation\CodeGeneratorBuilder;
use Axtiva\FlexibleGraphql\Builder\Foundation\Psr\Container\TypeRegistryGeneratorBuilder;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\CodeGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\LanguageLevelConfigInterface;
use Axtiva\FlexibleGraphql\Tests\Helper\FileSystemHelper;
use GraphQL\Language\Parser;
use GraphQL\Utils\BuildSchema;
use PHPUnit\Framework\TestCase;

class GeneratedCodePhpStanLevel6Test extends TestCase
{
    public function testGeneratedCodeIsPhpStanLevel6Clean(): void
    {
        $dir = uniqid('/tmp/TmpTestData/GraphQL', true);
        if ($dir === false) {
            $this->fail('Cannot create temporary directory path.');
        }

        $projectRoot = dirname(__DIR__, 2);
        $namespace = 'Axtiva\\FlexibleGraphql\\Tests\\Generated\\GraphQL';

        FileSystemHelper::rmdir($dir);
        FileSystemHelper::mkdir($dir);

        try {
            $schema = BuildSchema::build(Parser::parse(<<<'GQL'
directive @sum(x: Int) on FIELD | FIELD_DEFINITION

type Query {
    dynamicSum(x: Int!, y: Int!): Int @sum(x: 4)
}
GQL));

            $mainConfig = new CodeGeneratorConfig($dir, LanguageLevelConfigInterface::V8_3, $namespace);
            $codeGenerator = (new CodeGeneratorBuilder($mainConfig))->build();

            foreach ($codeGenerator->generateAllTypes($schema) as $filename);

            foreach (['sum'] as $directiveName) {
                $directive = $schema->getDirective($directiveName);
                self::assertNotNull($directive);
                foreach ($codeGenerator->generateDirectiveResolver($directive, $schema) as $item);
            }

            $typeRegistryGenerator = (new TypeRegistryGeneratorBuilder(
                new CodeGeneratorConfig($dir, LanguageLevelConfigInterface::V8_3, $namespace)
            ))->build();

            $bytes = file_put_contents(
                $typeRegistryGenerator->getConfig()->getTypeRegistryClassFileName(),
                $typeRegistryGenerator->generate($schema)
            );
            if ($bytes === false) {
                $this->fail('Cannot write generated TypeRegistry file.');
            }

            $phpstanCommand = sprintf(
                'php %s analyse -l 6 %s 2>&1',
                escapeshellarg($projectRoot . '/vendor/bin/phpstan'),
                escapeshellarg($dir)
            );

            $phpstanOutput = [];
            exec($phpstanCommand, $phpstanOutput, $phpstanExitCode);

            self::assertSame(
                0,
                $phpstanExitCode,
                "Generated code is not PHPStan level 6 clean.\n" .
                'Command: ' . $phpstanCommand . "\n" .
                implode(PHP_EOL, $phpstanOutput)
            );
        } finally {
            FileSystemHelper::rmdir($dir);
        }
    }
}
