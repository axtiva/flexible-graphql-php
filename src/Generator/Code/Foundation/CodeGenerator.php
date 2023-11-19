<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\Code\Foundation;

use Axtiva\FlexibleGraphql\Generator\Code\CodeGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\Config\ObjectGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Exception\FilesystemException;
use Axtiva\FlexibleGraphql\Generator\Exception\UnsupportedType;
use Axtiva\FlexibleGraphql\Generator\Model\ArgsDirectiveResolverModelGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\Model\ArgsFieldResolverModelGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\Model\DirectiveResolverGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\Model\FieldResolverGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\Model\Foundation\PHPParser\PropertyNodeVisitor;
use Axtiva\FlexibleGraphql\Generator\Model\ModelGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\Model\ObjectModelGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\Model\ScalarResolverGeneratorInterface;
use Axtiva\FlexibleGraphql\Resolver\AutoGenerationInterface;
use Axtiva\FlexibleGraphql\Utils\ObjectHelper;
use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType;
use GraphQL\Type\Schema;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;

class CodeGenerator implements CodeGeneratorInterface
{
    private iterable $generators;
    /**
     * @var FieldResolverGeneratorInterface[]
     */
    private array $fieldResolversGenerator;
    private Parser $parser;
    private ScalarResolverGeneratorInterface $scalarResolverGenerator;
    private DirectiveResolverGeneratorInterface $directiveResolverGenerator;
    private ArgsDirectiveResolverModelGeneratorInterface $argsDirectiveResolverGenerator;
    private ArgsFieldResolverModelGeneratorInterface $argsFieldResolverModelGenerator;

    public function __construct(
        array $fieldResolversGenerator,
        ScalarResolverGeneratorInterface $scalarResolverGenerator,
        DirectiveResolverGeneratorInterface $directiveResolverGenerator,
        ArgsDirectiveResolverModelGeneratorInterface $argsDirectiveResolverGenerator,
        ArgsFieldResolverModelGeneratorInterface $argsFieldResolverModelGenerator,
        ModelGeneratorInterface ...$generators
    ) {
        $this->parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $this->generators = $generators;
        $this->fieldResolversGenerator = $fieldResolversGenerator;
        $this->scalarResolverGenerator = $scalarResolverGenerator;
        $this->directiveResolverGenerator = $directiveResolverGenerator;
        $this->argsDirectiveResolverGenerator = $argsDirectiveResolverGenerator;
        $this->argsFieldResolverModelGenerator = $argsFieldResolverModelGenerator;
    }

    public function generateAllTypes(Schema $schema): iterable
    {
        /** @var ObjectType|UnionType $type */
        foreach ($schema->getTypeMap() as $type) {
            yield from $this->generateType($type, $schema);
        }
    }

    public function generateType(Type $type, Schema $schema): iterable
    {
        if ($type->name === 'Query' || $type->name === 'Mutation') {
            foreach ($type->getFields() as $field) {
                yield from $this->generateFieldResolver($type, $field, $schema);
            }
        } else {
            if ($this->isSupportedType($type) === false) {
                return ;
            }

            foreach ($this->getGenerators($type) as $generator) {
                $filename = $generator->getConfig()->getModelClassFileName($type);
                if ($generator instanceof ObjectModelGeneratorInterface) {
                    /**@var ObjectGeneratorConfigInterface $config */
                    $config = $generator->getConfig();
                    $classname = $config->getModelFullClassName($type);
                    if (! file_exists($filename)
                        || ObjectHelper::isClassImplements($classname, AutoGenerationInterface::class)
                    ) {
                        $filename = $config->getModelClassFileName($type);
                        $code = $generator->generate($type, $schema);
                        $this->saveFile($code, $filename);
                        yield new GeneratedCode($classname, $filename);
                    } else {
                        $stmts = $this->parser->parse(file_get_contents($filename));
                        $traverser = new NodeTraverser();
                        $collector = new PropertyNodeVisitor();
                        $traverser->addVisitor($collector);
                        $traverser->traverse($stmts);
                        $existedFields = [];
                        foreach ($collector->getResults() as $fieldName => $field) {
                            $existedFields[] = $fieldName;
                        }
                        foreach (($type instanceof ObjectType || $type instanceof InterfaceType) ? $type->getFields() : [] as $field) {
                            if (!in_array($field->name, $existedFields)) {
                                yield from $this->generateFieldResolver($type, $field, $schema);
                            }
                        }
                    }
                } else {
                    /**@var ModelGeneratorInterface $generator */
                    $classname = $generator->getConfig()->getModelFullClassName($type);
                    $filename = $generator->getConfig()->getModelClassFileName($type);
                    $code = $generator->generate($type, $schema);
                    $this->saveFile($code, $filename);
                    yield new GeneratedCode($classname, $filename);
                }
            }
        }
    }

    public function generateScalarResolver(CustomScalarType $scalar, Schema $schema): GeneratedCode
    {
        $classname = $this->scalarResolverGenerator->getConfig()->getModelFullClassName($scalar);
        $filename = $this->scalarResolverGenerator->getConfig()->getModelClassFileName($scalar);
        if (! file_exists($filename)) {
            $code = $this->scalarResolverGenerator->generate($scalar, $schema);
            $this->saveFile($code, $filename);
        }

        return new GeneratedCode($classname, $filename);
    }

    public function generateDirectiveResolver(Directive $directive, Schema $schema): iterable
    {
        $classname = $this->directiveResolverGenerator->getConfig()->getDirectiveResolverFullClassName($directive);
        $filename = $this->directiveResolverGenerator->getConfig()->getDirectiveResolverClassFileName($directive);
        if (! file_exists($filename)) {
            $code = $this->directiveResolverGenerator->generate($directive, $schema);
            $this->saveFile($code, $filename);
        }

        yield new GeneratedCode($classname, $filename);

        if ($this->argsDirectiveResolverGenerator->isSupportedType($directive)) {
            $classname = $this->argsDirectiveResolverGenerator->getConfig()->getDirectiveArgsFullClassName($directive);
            $filename = $this->argsDirectiveResolverGenerator->getConfig()->getDirectiveArgsClassFileName($directive);

            $code = $this->argsDirectiveResolverGenerator->generate($directive, $schema);
            $this->saveFile($code, $filename);

            yield new GeneratedCode($classname, $filename);
        }
    }

    public function generateFieldResolver(Type $type, FieldDefinition $field, Schema $schema): iterable
    {
        foreach ($this->fieldResolversGenerator as $generator) {
            if ($generator->isSupportedType($type, $field)) {
                $classname = $generator->getConfig()->getFieldResolverFullClassName($type, $field);
                $filename = $generator->getConfig()->getFieldResolverClassFileName($type, $field);
                if (!file_exists($filename)) {
                    $code = $generator->generate($type, $field, $schema);
                    $this->saveFile($code, $filename);
                }
                yield new GeneratedCode($classname, $filename);

                if ($this->argsFieldResolverModelGenerator->isSupportedType($type, $field)) {
                    $classname = $this->argsFieldResolverModelGenerator->getConfig()->getFieldArgsFullClassName($type, $field);
                    $filename = $this->argsFieldResolverModelGenerator->getConfig()->getFieldArgsClassFileName($type, $field);

                    $code = $this->argsFieldResolverModelGenerator->generate($type, $field, $schema);
                    $this->saveFile($code, $filename);

                    yield new GeneratedCode($classname, $filename);
                }

                return;
            }
        }

        throw new UnsupportedType($type->toString() . '.' . $field->getName());
    }

    private function isSupportedType(Type $type): bool
    {
        foreach ($this->generators as $generator) {
            if ($generator->isSupportedType($type)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Type $type
     * @return ModelGeneratorInterface[]
     */
    private function getGenerators(Type $type): iterable
    {
        $hasGenerator = false;
        foreach ($this->generators as $generator) {
            if ($generator->isSupportedType($type)) {
                $hasGenerator = true;
                yield $generator;
            }
        }

        if ($hasGenerator === false) {
            throw new UnsupportedType($type->toString());
        }
    }

    private function saveFile(string $code, $filename): void
    {
        $dirName = dirname($filename);
        if (!file_exists($dirName)) {
            if (! mkdir($dirName, 0777, true)) {
                throw new FilesystemException($dirName);
            }
        }

        if (!file_put_contents($filename, $code)) {
            throw new FilesystemException($filename);
        }
    }
}