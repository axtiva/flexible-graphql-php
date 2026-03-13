<?php

namespace Axtiva\FlexibleGraphql\Tests\Generator\ResolverProvider;

use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\CodeGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\DirectiveResolverGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\ResolverProvider\Foundation\ContainerCallDirectiveResolverProvider;
use Axtiva\FlexibleGraphql\Tests\Helper\FixtureLoader;
use Axtiva\FlexibleGraphql\Tests\Helper\FileSystemHelper;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ContainerCallDirectiveResolverProviderTest extends TestCase
{
    /**
     * @return void
     * @dataProvider dataProviderGeneratePhpCode
     */
#[DataProvider('dataProviderGeneratePhpCode')]
    public function testGeneratePhpCode(
        string $directiveName,
        string $languageLevel,
        Schema $schema,
        string $expectedFixturePath
    ) {
        $namespace = 'Axtiva\FlexibleGraphql\Example\GraphQL';
        $dir = '/tmp/TmpTestData/GraphQL';

        FileSystemHelper::rmdir($dir);
        FileSystemHelper::mkdir($dir);

        $mainConfig = new CodeGeneratorConfig($dir, $languageLevel, $namespace);
        $directiveConfig = new DirectiveResolverGeneratorConfig($mainConfig);
        $generator = new ContainerCallDirectiveResolverProvider();

        /** @var Directive $directive */
        $directive = $schema->getDirective($directiveName);
        $this->assertInstanceOf(Directive::class, $directive);

        $generated = $generator->generate($directiveConfig, $directive);
        $expected = FixtureLoader::load($expectedFixturePath);
        $this->assertSame($expected, FixtureLoader::normalizeLineEndings($generated));

        FileSystemHelper::rmdir($dir);
    }

    /**
     * @return iterable<int, array<int, mixed>>
     */
    public static function dataProviderGeneratePhpCode(): iterable
    {
        yield [
            'uppercase',
            CodeGeneratorConfig::V8_3,
            BuildSchema::build(Parser::parse(<<<GQL
"CAPITALIZE ALL LETTERS IN STRING"
directive @uppercase on FIELD | FIELD_DEFINITION
GQL)),
            __DIR__ . '/fixtures/ContainerCallDirectiveResolverProviderTest/case-1.php.txt',
        ];
    }
}
