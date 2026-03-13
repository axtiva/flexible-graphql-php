<?php

namespace Axtiva\FlexibleGraphql\Tests\Generator\Model\Psr4;

use Axtiva\FlexibleGraphql\Builder\Foundation\CodeGeneratorBuilder;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\CodeGeneratorConfig;
use Axtiva\FlexibleGraphql\Tests\Helper\FixtureLoader;
use Axtiva\FlexibleGraphql\Tests\Helper\FileSystemHelper;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ScalarResolverGeneratorTest extends TestCase
{
    /**
     * @return void
     * @dataProvider dataProviderGeneratePhpCode
     */
#[DataProvider('dataProviderGeneratePhpCode')]
    public function testGeneratePhpCode(
        string $typeName,
        string $languageLevel,
        Schema $schema,
        string $expectedFixturePath
    ) {
        $namespace = 'Axtiva\FlexibleGraphql\Example\GraphQL';
        $dir = uniqid('/tmp/TmpTestData/GraphQL');

        FileSystemHelper::rmdir($dir);
        FileSystemHelper::mkdir($dir);

        $builder = new CodeGeneratorBuilder(new CodeGeneratorConfig($dir, $languageLevel, $namespace));

        $generator = $builder->build();

        /** @var CustomScalarType $type */
        $type = $schema->getType($typeName);
        $this->assertInstanceOf(CustomScalarType::class, $type);

        $code = $generator->generateScalarResolver($type, $schema);

        $generated = file_get_contents($code->getFilename());
        $this->assertNotFalse($generated);
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
            'DateTime',
            CodeGeneratorConfig::V8_3,
            BuildSchema::build(Parser::parse(<<<GQL
scalar DateTime
GQL
            )),__DIR__ . '/fixtures/ScalarResolverGeneratorTest/case-1.php.txt'
            ,];
    }
}
