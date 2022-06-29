<?php

namespace Axtiva\FlexibleGraphql\Tests\Generator\Model\Psr4;

use Axtiva\FlexibleGraphql\Builder\Foundation\CodeGeneratorBuilder;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\CodeGeneratorConfig;
use Axtiva\FlexibleGraphql\Tests\Helper\FileSystemHelper;
use GraphQL\Language\Parser;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use PHPUnit\Framework\TestCase;

class DirectiveResolverGeneratorTest extends TestCase
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
        $dir = uniqid('/tmp/TmpTestData/GraphQL');

        FileSystemHelper::rmdir($dir);
        FileSystemHelper::mkdir($dir);

        $builder = new CodeGeneratorBuilder(new CodeGeneratorConfig($dir, $languageLevel, $namespace));

        $generator = $builder->build();

        $directive = $schema->getDirective($directiveName);
        foreach ($generator->generateDirectiveResolver($directive, $schema) as $item) {
            $code = $item;
            break;
        }
        $this->assertTrue(isset($code));
        $this->assertEquals($expected, file_get_contents($code->getFilename()));

        FileSystemHelper::rmdir($dir);
    }

    public function dataProviderGeneratePhpCode(): iterable
    {
        yield [
            'uppercase',
            CodeGeneratorConfig::V7_4,
            BuildSchema::build(Parser::parse(<<<GQL
"CAPITALIZE ALL LETTERS IN STRING"
directive @uppercase on FIELD | FIELD_DEFINITION
GQL
)),<<<'PHP'
<?php

declare (strict_types=1);
namespace Axtiva\FlexibleGraphql\Example\GraphQL\Directive;

use Axtiva\FlexibleGraphql\Generator\Exception\NotImplementedResolver;
use GraphQL\Type\Definition\ResolveInfo;
use Axtiva\FlexibleGraphql\Resolver\DirectiveResolverInterface;

/**
 * This code is @generated by axtiva/flexible-graphql-php
 * Resolver for executable directive @uppercase
 * CAPITALIZE ALL LETTERS IN STRING
 */
final class UppercaseDirective implements DirectiveResolverInterface
{
    /**
     * @param callable $next
     * @param $directiveArgs
     * @param $rootValue
     * @param $args
     * @param $context
     * @param ResolveInfo $info
     * @return mixed
     */
    public function __invoke(callable $next, $directiveArgs, $rootValue, $args, $context, ResolveInfo $info)
    {
        throw new NotImplementedResolver('Not implemented directive resolver ' . __CLASS__);
        // FIXME example return mb_strtoupper($next($rootValue, $args, $context, $info));
    }
}
PHP
,];

        yield [
            'sum',
            CodeGeneratorConfig::V7_4,
            BuildSchema::build(Parser::parse(<<<GQL
directive @sum(x: Int) on FIELD | FIELD_DEFINITION
GQL
            )),<<<'PHP'
<?php

declare (strict_types=1);
namespace Axtiva\FlexibleGraphql\Example\GraphQL\Directive;

use Axtiva\FlexibleGraphql\Generator\Exception\NotImplementedResolver;
use GraphQL\Type\Definition\ResolveInfo;
use Axtiva\FlexibleGraphql\Resolver\DirectiveResolverInterface;
use Axtiva\FlexibleGraphql\Example\GraphQL\DirectiveArgs\SumDirectiveArgs;

/**
 * This code is @generated by axtiva/flexible-graphql-php
 * Resolver for executable directive @sum
 */
final class SumDirective implements DirectiveResolverInterface
{
    /**
     * @param callable $next
     * @param SumDirectiveArgs $directiveArgs
     * @param $rootValue
     * @param $args
     * @param $context
     * @param ResolveInfo $info
     * @return mixed
     */
    public function __invoke(callable $next, $directiveArgs, $rootValue, $args, $context, ResolveInfo $info)
    {
        throw new NotImplementedResolver('Not implemented directive resolver ' . __CLASS__);
        // FIXME example return mb_strtoupper($next($rootValue, $args, $context, $info));
    }
}
PHP
            ,];
    }
}
