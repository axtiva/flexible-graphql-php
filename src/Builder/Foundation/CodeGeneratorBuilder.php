<?php

namespace Axtiva\FlexibleGraphql\Builder\Foundation;

use Axtiva\FlexibleGraphql\Builder\CodeGeneratorBuilderInterface;
use Axtiva\FlexibleGraphql\Generator\Code\CodeGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\Code\Foundation\CodeGenerator;
use Axtiva\FlexibleGraphql\Generator\Config\ArgsDirectiveResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\ArgsFieldResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\CodeGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\DirectiveResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\ArgsDirectiveResolverGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\ArgsFieldResolverGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\CodeGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\DirectiveResolverGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\EnumModelGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\FieldResolverGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\InputObjectGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\InterfaceGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\ObjectGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\ScalarResolverGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\UnionModelGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\UnionResolveTypeGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\InputObjectGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\ObjectGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\ScalarResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\UnionObjectGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\UnionResolveTypeGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Model\ArgsDirectiveResolverModelGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\Model\ArgsFieldResolverModelGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\Model\DirectiveResolverGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\Model\EnumModelGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\Model\FieldResolverGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\Model\Foundation\Psr4\ArgsDirectiveResolverModelGenerator;
use Axtiva\FlexibleGraphql\Generator\Model\Foundation\Psr4\ArgsFieldResolverModelGenerator;
use Axtiva\FlexibleGraphql\Generator\Model\Foundation\Psr4\DirectiveResolverGenerator;
use Axtiva\FlexibleGraphql\Generator\Model\Foundation\Psr4\EnumModelGenerator;
use Axtiva\FlexibleGraphql\Generator\Model\Foundation\Psr4\FieldResolverGenerator;
use Axtiva\FlexibleGraphql\Generator\Model\Foundation\Psr4\InputObjectModelGenerator;
use Axtiva\FlexibleGraphql\Generator\Model\Foundation\Psr4\InterfaceModelGenerator;
use Axtiva\FlexibleGraphql\Generator\Model\Foundation\Psr4\ObjectModelGenerator;
use Axtiva\FlexibleGraphql\Generator\Model\Foundation\Psr4\ScalarResolverGenerator;
use Axtiva\FlexibleGraphql\Generator\Model\Foundation\Psr4\UnionModelGenerator;
use Axtiva\FlexibleGraphql\Generator\Model\Foundation\Psr4\UnionResolveTypeModelGenerator;
use Axtiva\FlexibleGraphql\Generator\Model\InputObjectModelGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\Model\InterfaceModelGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\Model\ModelGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\Model\ObjectModelGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\Model\RootObjectModelGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\Model\ScalarResolverGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\Model\UnionResolveTypeModelGeneratorInterface;

class CodeGeneratorBuilder implements CodeGeneratorBuilderInterface
{
    private CodeGeneratorConfigInterface $config;
    /**
     * @var FieldResolverGeneratorInterface[]
     */
    private array $fieldResolverGenerators = [];
    private ?EnumModelGeneratorInterface $enumModelGenerator = null;
    private ?InterfaceModelGeneratorInterface $interfaceModelGenerator = null;
    private ?ObjectModelGeneratorInterface $objectModelGenerator = null;
    private ?ModelGeneratorInterface $unionModelGenerator = null;
    private ?UnionResolveTypeModelGeneratorInterface $unionResolveTypeModelGenerator = null;
    private ?ScalarResolverGeneratorInterface $scalarResolverGenerator = null;
    private ?DirectiveResolverGeneratorInterface $directiveResolverGenerator = null;
    private ?ArgsDirectiveResolverModelGeneratorInterface $argsDirectiveResolverGenerator = null;
    private ?ArgsFieldResolverModelGeneratorInterface $argsFieldResolverGenerator = null;
    private ?InputObjectModelGeneratorInterface $inputObjectModelGenerator = null;
    /** @var ModelGeneratorInterface[] */
    private array $modelGenerators = [];
    private ObjectGeneratorConfigInterface $objectGeneratorConfig;
    private UnionObjectGeneratorConfigInterface $unionModelConfig;
    private UnionResolveTypeGeneratorConfigInterface $unionResolveTypeModelConfig;
    private InterfaceGeneratorConfig $interfaceGeneratorConfig;
    private EnumModelGeneratorConfig $enumConfig;
    private ScalarResolverGeneratorConfigInterface $scalarConfig;
    private DirectiveResolverGeneratorConfigInterface $directiveConfig;
    private ArgsDirectiveResolverGeneratorConfigInterface $argsDirectiveConfig;
    private ArgsFieldResolverGeneratorConfigInterface $argsFieldConfig;
    private InputObjectGeneratorConfigInterface $inputObjectConfig;

    public function __construct(CodeGeneratorConfigInterface $config)
    {
        $this->config = $config;
        $this->objectGeneratorConfig = new ObjectGeneratorConfig($this->config);
        $this->interfaceGeneratorConfig = new InterfaceGeneratorConfig($this->config);
        $this->unionModelConfig = new UnionModelGeneratorConfig($this->config);
        $this->unionResolveTypeModelConfig = new UnionResolveTypeGeneratorConfig($this->config);
        $this->enumConfig = new EnumModelGeneratorConfig($this->config);
        $this->scalarConfig = new ScalarResolverGeneratorConfig($this->config);
        $this->directiveConfig = new DirectiveResolverGeneratorConfig($this->config);
        $this->inputObjectConfig = new InputObjectGeneratorConfig($this->config);
        $this->argsDirectiveConfig = new ArgsDirectiveResolverGeneratorConfig($this->config);
        $this->argsFieldConfig = new ArgsFieldResolverGeneratorConfig($this->config);
    }

    public function getConfig(): CodeGeneratorConfigInterface
    {
        return $this->config;
    }

    public function setEnumGenerator(EnumModelGeneratorInterface $enumModelGenerator): void
    {
        $this->enumModelGenerator = $enumModelGenerator;
    }

    public function setInterfaceGenerator(InterfaceModelGeneratorInterface $interfaceModelGenerator): void
    {
        $this->interfaceModelGenerator = $interfaceModelGenerator;
    }

    public function setObjectGenerator(ObjectModelGeneratorInterface $objectModelGenerator): void
    {
        $this->objectModelGenerator = $objectModelGenerator;
    }

    public function setUnionGenerator(ModelGeneratorInterface $unionModelGenerator): void
    {
        $this->unionModelGenerator = $unionModelGenerator;
    }

    public function setUnionResolveTypeGenerator(
        UnionResolveTypeModelGeneratorInterface $unionResolveTypeModelGenerator
    ): void {
        $this->unionResolveTypeModelGenerator = $unionResolveTypeModelGenerator;
    }

    public function setDirectiveResolverGenerator(
        DirectiveResolverGeneratorInterface $directiveResolverGenerator
    ): void {
        $this->directiveResolverGenerator = $directiveResolverGenerator;
    }

    public function setArgsDirectiveResolverGenerator(
        ArgsDirectiveResolverModelGeneratorInterface $argsDirectiveResolverGenerator
    ): void {
        $this->argsDirectiveResolverGenerator = $argsDirectiveResolverGenerator;
    }

    public function setArgsFieldResolverGenerator(
        ArgsFieldResolverModelGeneratorInterface $argsFieldResolverGenerator
    ): void {
        $this->argsFieldResolverGenerator = $argsFieldResolverGenerator;
    }

    public function setScalarResolverGenerator(ScalarResolverGeneratorInterface $scalarResolverGenerator): void
    {
        $this->scalarResolverGenerator = $scalarResolverGenerator;
    }

    public function setInputObjectModelGenerator(InputObjectModelGeneratorInterface $inputObjectModelGenerator): void
    {
        $this->inputObjectModelGenerator = $inputObjectModelGenerator;
    }

    public function addFieldResolverGenerator(FieldResolverGeneratorInterface $fieldResolverGenerator): void
    {
        $this->fieldResolverGenerators[] = $fieldResolverGenerator;
    }

    public function addModelGenerator(ModelGeneratorInterface $generator): void
    {
        $this->modelGenerators[] = $generator;
    }

    public function build(): CodeGeneratorInterface
    {
        $this->addFieldResolverGenerator(
            new FieldResolverGenerator(
                new FieldResolverGeneratorConfig($this->getConfig()),
                $this->objectGeneratorConfig,
                $this->scalarConfig,
                $this->enumConfig,
                $this->unionModelConfig,
                $this->interfaceGeneratorConfig,
                $this->argsFieldConfig,
            )
        );

        if (empty($this->argsFieldResolverGenerator)) {
            $this->argsFieldResolverGenerator = new ArgsFieldResolverModelGenerator(
                $this->argsFieldConfig,
                $this->scalarConfig,
                $this->enumConfig,
                $this->inputObjectConfig,
            );
        }

        if (empty($this->enumModelGenerator)) {
            $this->enumModelGenerator = new EnumModelGenerator(
                $this->enumConfig,
            );
        }

        if (empty($this->interfaceModelGenerator)) {
            $this->interfaceModelGenerator = new InterfaceModelGenerator(
                $this->interfaceGeneratorConfig,
            );
        }

        if (empty($this->objectModelGenerator)) {
            $this->objectModelGenerator = new ObjectModelGenerator(
                $this->objectGeneratorConfig,
                $this->unionModelConfig,
                $this->scalarConfig,
                $this->interfaceGeneratorConfig,
                $this->enumConfig,
            );
        }

        if (empty($this->unionModelGenerator)) {
            $this->unionModelGenerator = new UnionModelGenerator(
                $this->unionModelConfig,
            );
        }

        if (empty($this->unionResolveTypeModelGenerator)) {
            $this->unionResolveTypeModelGenerator = new UnionResolveTypeModelGenerator(
                $this->unionResolveTypeModelConfig,
                $this->objectModelGenerator,
            );
        }

        if (empty($this->scalarResolverGenerator)) {
            $this->scalarResolverGenerator = new ScalarResolverGenerator(
                $this->scalarConfig,
            );
        }

        if (empty($this->directiveResolverGenerator)) {
            $this->directiveResolverGenerator = new DirectiveResolverGenerator(
                $this->directiveConfig,
                $this->argsDirectiveConfig,
            );
        }

        if (empty($this->argsDirectiveResolverGenerator)) {
            $this->argsDirectiveResolverGenerator = new ArgsDirectiveResolverModelGenerator(
                $this->argsDirectiveConfig,
                $this->scalarConfig,
                $this->enumConfig,
                $this->inputObjectConfig,
            );
        }

        if (empty($this->inputObjectModelGenerator)) {
            $this->inputObjectModelGenerator = new InputObjectModelGenerator(
                $this->inputObjectConfig,
                $this->scalarConfig,
                $this->enumConfig,
            );
        }

        return new CodeGenerator(
            $this->fieldResolverGenerators,
            $this->scalarResolverGenerator,
            $this->directiveResolverGenerator,
            $this->argsDirectiveResolverGenerator,
            $this->argsFieldResolverGenerator,
            $this->enumModelGenerator,
            $this->inputObjectModelGenerator,
            $this->interfaceModelGenerator,
            $this->objectModelGenerator,
            $this->unionModelGenerator,
            $this->unionResolveTypeModelGenerator,
            ...$this->modelGenerators,
        );
    }
}