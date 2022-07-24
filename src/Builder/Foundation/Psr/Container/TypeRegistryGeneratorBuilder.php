<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Builder\Foundation\Psr\Container;

use Axtiva\FlexibleGraphql\Builder\TypeRegistryGeneratorBuilderInterface;
use Axtiva\FlexibleGraphql\Generator\Config\ArgsDirectiveResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\ArgsFieldResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\CodeGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\DirectiveResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\FieldResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\ArgsDirectiveResolverGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\ArgsFieldResolverGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\DirectiveResolverGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\FederationFieldResolverGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\FieldResolverGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\ScalarResolverGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\TypeRegistryGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\UnionResolveTypeGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\ScalarResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\TypeRegistryGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\UnionResolveTypeGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\ResolverProvider\DirectiveResolverProviderInterface;
use Axtiva\FlexibleGraphql\Generator\ResolverProvider\Foundation\ContainerCallDirectiveResolverProvider;
use Axtiva\FlexibleGraphql\Generator\ResolverProvider\Foundation\ContainerCallFieldResolverProvider;
use Axtiva\FlexibleGraphql\Generator\ResolverProvider\Foundation\ContainerCallScalarResolverProvider;
use Axtiva\FlexibleGraphql\Generator\ResolverProvider\Foundation\ContainerCallUnionResolverProvider;
use Axtiva\FlexibleGraphql\Generator\ResolverProvider\Foundation\WrappedContainerCallFieldResolverProvider;
use Axtiva\FlexibleGraphql\Generator\ResolverProvider\FieldResolverProviderInterface;
use Axtiva\FlexibleGraphql\Generator\ResolverProvider\ScalarResolverProviderInterface;
use Axtiva\FlexibleGraphql\Generator\ResolverProvider\UnionResolverProviderInterface;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\BooleanGenerator;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\CompositeScalarTypeGenerator;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\CompositeTypeGenerator;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\CustomScalarGenerator;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\DirectiveGenerator;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\DirectiveRegistryMethodGenerator;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\EnumGenerator;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\FieldArgumentGenerator;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\FieldDefinitionGenerator;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\FloatGenerator;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\IDGenerator;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\InputObjectFieldDefinitionGenerator;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\InputObjectGenerator;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\InterfaceGenerator;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\IntGenerator;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\ObjectGenerator;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\StringGenerator;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\TypeDefinitionResolver;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\TypeRegistryMethodCallGenerator;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\TypeRegistryMethodGenerator;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\TypeRegistryMethodNameGenerator;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\TypeRegistryPsrContainerGenerator;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\UnionGenerator;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\Resolver;
use Axtiva\FlexibleGraphql\Generator\Serializer\Foundation\VariableSerializer;
use Axtiva\FlexibleGraphql\Generator\Serializer\VariableSerializerInterface;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\TypeRegistryGeneratorInterface;

class TypeRegistryGeneratorBuilder implements TypeRegistryGeneratorBuilderInterface
{
    protected FieldResolverProviderInterface $wrappedContainerCallGenerator;
    protected FieldResolverProviderInterface $fieldResolverProvider;
    protected DirectiveResolverProviderInterface $directiveResolverProvider;
    protected UnionResolverProviderInterface $unionResolverProvider;
    protected ScalarResolverProviderInterface $scalarResolverProvider;
    protected ?string $defaultResolverServiceName = null;
    protected VariableSerializerInterface $serializer;
    protected TypeRegistryGeneratorConfigInterface $registryConfig;
    protected CodeGeneratorConfigInterface $config;
    protected ?FieldResolverGeneratorConfigInterface $fieldResolverGeneratorConfig = null;
    protected ?DirectiveResolverGeneratorConfigInterface $directiveResolverGeneratorConfig = null;
    protected ?UnionResolveTypeGeneratorConfigInterface $unionResolveTypeGeneratorConfig = null;
    protected ?ScalarResolverGeneratorConfigInterface $scalarResolverGeneratorConfig = null;
    protected ?ArgsDirectiveResolverGeneratorConfigInterface $argsDirectiveResolverGeneratorConfig = null;
    protected ?ArgsFieldResolverGeneratorConfigInterface $argsFieldResolverGeneratorConfig = null;

    public function __construct(CodeGeneratorConfigInterface $config)
    {
        $this->config = $config;
        $this->registryConfig = new TypeRegistryGeneratorConfig($this->config);
        $this->fieldResolverProvider = new ContainerCallFieldResolverProvider();
        $this->directiveResolverProvider = new ContainerCallDirectiveResolverProvider();
        $this->unionResolverProvider = new ContainerCallUnionResolverProvider();
        $this->scalarResolverProvider = new ContainerCallScalarResolverProvider();
        $this->serializer = new VariableSerializer();
    }

    public function getConfig(): TypeRegistryGeneratorConfigInterface
    {
        return $this->registryConfig;
    }

    public function setVariableSerializer(VariableSerializerInterface $serializer): void
    {
        $this->serializer = $serializer;
    }

    public function setFieldResolverProvider(FieldResolverProviderInterface $generator): void
    {
        $this->fieldResolverProvider = $generator;
    }

    public function setDirectiveResolverProvider(DirectiveResolverProviderInterface $generator): void
    {
        $this->directiveResolverProvider = $generator;
    }

    public function setUnionResolverProvider(UnionResolverProviderInterface $generator): void
    {
        $this->unionResolverProvider = $generator;
    }

    public function setScalarResolverProvider(ScalarResolverProviderInterface $generator): void
    {
        $this->scalarResolverProvider = $generator;
    }

    public function setDefaultFieldResolver(string $defaultResolverServiceName): void
    {
        $this->defaultResolverServiceName = $defaultResolverServiceName;
    }

    public function setFieldResolverGeneratorConfig(FieldResolverGeneratorConfigInterface $config)
    {
        $this->fieldResolverGeneratorConfig = $config;
    }

    public function setDirectiveResolverGeneratorConfig(DirectiveResolverGeneratorConfigInterface $config)
    {
        $this->directiveResolverGeneratorConfig = $config;
    }

    public function setUnionResolveGeneratorConfig(UnionResolveTypeGeneratorConfigInterface $config)
    {
        $this->unionResolveTypeGeneratorConfig = $config;
    }

    public function setScalarResolveGeneratorConfig(ScalarResolverGeneratorConfigInterface $config)
    {
        $this->scalarResolverGeneratorConfig = $config;
    }

    public function setArgsDirectiveResolveGeneratorConfig(ArgsDirectiveResolverGeneratorConfigInterface $config)
    {
        $this->argsDirectiveResolverGeneratorConfig = $config;
    }

    public function setArgsFieldResolveGeneratorConfig(ArgsFieldResolverGeneratorConfigInterface $config)
    {
        $this->argsFieldResolverGeneratorConfig = $config;
    }

    public function build(): TypeRegistryGeneratorInterface
    {
        $this->directiveResolverGeneratorConfig ??= new DirectiveResolverGeneratorConfig($this->config);
        $this->fieldResolverGeneratorConfig ??= new FieldResolverGeneratorConfig($this->config);
        $this->unionResolveTypeGeneratorConfig ??= new UnionResolveTypeGeneratorConfig($this->config);
        $this->scalarResolverGeneratorConfig ??= new ScalarResolverGeneratorConfig($this->config);
        $this->argsDirectiveResolverGeneratorConfig ??= new ArgsDirectiveResolverGeneratorConfig($this->config);
        $this->argsFieldResolverGeneratorConfig ??= new ArgsFieldResolverGeneratorConfig($this->config);

        $this->wrappedContainerCallGenerator = new WrappedContainerCallFieldResolverProvider(
            $this->fieldResolverProvider,
            $this->argsFieldResolverGeneratorConfig
        );

        $directiveResolver = new Resolver\Psr\Container\DirectiveGenerator(
            $this->directiveResolverGeneratorConfig,
            $this->directiveResolverProvider
        );

        if ($this->defaultResolverServiceName) {
            $defaultResolver = new Resolver\Psr\Container\DefaultFieldGenerator(
                sprintf('$this->container->get(\'%s\')', $this->defaultResolverServiceName)
            );
        } else {
            $defaultResolver = new Resolver\DefaultResolver\FieldGenerator();
        }

        $fieldGenerator = new Resolver\Psr\Container\FieldGenerator(
            $this->fieldResolverGeneratorConfig,
            $this->wrappedContainerCallGenerator,
        );

        $fieldResolverWithDirectiveGenerator = new Resolver\Wrapper\FieldResolverDirectiveWrapped(
            $this->serializer,
            $fieldGenerator,
            $defaultResolver,
            $directiveResolver,
            $this->argsDirectiveResolverGeneratorConfig
        );

        $unionTypeResolver = new Resolver\Psr\Container\UnionTypeGenerator(
            $this->unionResolveTypeGeneratorConfig,
            $this->unionResolverProvider
        );
        $scalarResolver = new Resolver\Psr\Container\ScalarGenerator(
            $this->scalarResolverGeneratorConfig,
            $this->scalarResolverProvider,
        );

        $typeRegistryMethodNameGenerator = new TypeRegistryMethodNameGenerator();
        $typeRegistryMethodCallGenerator = new TypeRegistryMethodCallGenerator($this->serializer);

        $scalarTypeGenerator = new CompositeScalarTypeGenerator(
            new BooleanGenerator(),
            new FloatGenerator(),
            new IDGenerator(),
            new IntGenerator(),
            new StringGenerator(),
        );

        $typeDefinition = new TypeDefinitionResolver(
            $scalarTypeGenerator,
            $typeRegistryMethodCallGenerator
        );

        $fieldArgumentGenerator = new FieldArgumentGenerator(
            $this->serializer,
            $typeDefinition,
        );

        $inputObjectFieldGenerator = new InputObjectFieldDefinitionGenerator($this->serializer, $typeDefinition);
        $fieldDefinitionGenerator = new FieldDefinitionGenerator(
            $this->serializer,
            $fieldResolverWithDirectiveGenerator,
            $typeDefinition,
            $fieldArgumentGenerator
        );

        $objectTypeGenerator = new CompositeTypeGenerator(
            new InputObjectGenerator(
                $this->serializer,
                $inputObjectFieldGenerator,
            ),
            new InterfaceGenerator(
                $this->serializer,
                $fieldDefinitionGenerator
            ),
            new ObjectGenerator(
                $this->serializer,
                $fieldDefinitionGenerator
            ),
        );

        $customScalarGenerator = new CustomScalarGenerator(
            $this->serializer,
            $scalarResolver
        );

        $typeGenerator = new CompositeTypeGenerator(
            $scalarTypeGenerator,
            $objectTypeGenerator,
            new UnionGenerator(
                $this->serializer,
                $typeRegistryMethodCallGenerator,
                $unionTypeResolver,
            ),
            new EnumGenerator($this->serializer),
            $customScalarGenerator,
        );

        $directiveGenerator = new DirectiveGenerator($this->serializer, $fieldArgumentGenerator);

        $typeRegistryMethodGenerator = new TypeRegistryMethodGenerator(
            $typeGenerator, $typeRegistryMethodNameGenerator
        );
        $directiveRegistryMethodGenerator = new DirectiveRegistryMethodGenerator($directiveGenerator);

        return new TypeRegistryPsrContainerGenerator(
            $this->registryConfig,
            $typeRegistryMethodGenerator,
            $directiveRegistryMethodGenerator
        );
    }
}