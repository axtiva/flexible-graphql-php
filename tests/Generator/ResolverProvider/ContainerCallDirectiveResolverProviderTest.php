<?php

namespace Axtiva\FlexibleGraphql\Tests\Generator\ResolverProvider;

use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\CodeGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\DirectiveResolverGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\ResolverProvider\Foundation\ContainerCallDirectiveResolverProvider;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use PHPUnit\Framework\TestCase;

class ContainerCallDirectiveResolverProviderTest extends TestCase
{
    /**
     * @return void
     * @dataProvider dataProviderGeneratePhpCode
     */
    public function testGeneratePhpCode(
        string $directiveName,
        string $languageLevel,
        Schema $schema,
        string $expected
    ) {
        $namespace = 'Axtiva\FlexibleGraphql\Example\GraphQL';
        $dir = '/tmp/TmpTestData/GraphQL';

        $mainConfig = new CodeGeneratorConfig($dir, $languageLevel, $namespace);
        $directiveConfig = new DirectiveResolverGeneratorConfig($mainConfig);
        $generator = new ContainerCallDirectiveResolverProvider();

        /** @var Directive $directive */
        $directive = $schema->getDirective($directiveName);
        $this->assertInstanceOf(Directive::class, $directive);

        $this->assertEquals($expected, $generator->generate($directiveConfig, $directive));
    }

    public function dataProviderGeneratePhpCode(): iterable
    {
        yield [
            'uppercase',
            CodeGeneratorConfig::V7_4,
            BuildSchema::build(Parser::parse(<<<GQL
"CAPITALIZE ALL LETTERS IN STRING"
directive @uppercase on FIELD | FIELD_DEFINITION
GQL)),
            <<<'PHP'
$this->container->get('Axtiva\FlexibleGraphql\Example\GraphQL\Directive\UppercaseDirective')
PHP,
        ];
    }
}