<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Builder\Foundation\Psr\Container;

use Axtiva\FlexibleGraphql\Builder\TypeRegistryGeneratorBuilderInterface;
use Axtiva\FlexibleGraphql\Generator\Config\CodeGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\CodeGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\DirectiveResolverGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\FieldResolverGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\ScalarResolverGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\TypeRegistryGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\UnionResolveTypeGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\TypeRegistryGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\ResolverProvider\Foundation\WrappedContainerCallResolverProvider;
use Axtiva\FlexibleGraphql\Generator\ResolverProvider\ResolverProviderInterface;
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
    private ResolverProviderInterface $containerCallGenerator;
    private ?string $defaultResolverServiceName = null;
    private VariableSerializerInterface $serializer;
    private TypeRegistryGeneratorConfigInterface $registryConfig;
    private CodeGeneratorConfigInterface $config;

    public function __construct(string $dir, string $namespace = null)
    {
        $this->config = new CodeGeneratorConfig($dir, $namespace);
        $this->registryConfig = new TypeRegistryGeneratorConfig($this->config);
        $this->containerCallGenerator = new WrappedContainerCallResolverProvider();
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

    public function setContainerCallGenerator(ResolverProviderInterface $generator): void
    {
        $this->containerCallGenerator = $generator;
    }

    public function setDefaultFieldResolver(string $defaultResolverServiceName): void
    {
        $this->defaultResolverServiceName = $defaultResolverServiceName;
    }

    public function build(): TypeRegistryGeneratorInterface
    {
        $directiveResolver = new Resolver\Psr\Container\DirectiveGenerator(
            new DirectiveResolverGeneratorConfig($this->config),
            $this->containerCallGenerator
        );

        if ($this->defaultResolverServiceName) {
            $defaultResolver = new Resolver\Psr\Container\DefaultFieldGenerator(
                $this->defaultResolverServiceName,
                $this->containerCallGenerator
            );
        } else {
            $defaultResolver = new Resolver\DefaultResolver\FieldGenerator();
        }

        $fieldGenerator = new Resolver\Psr\Container\FieldGenerator(
            new FieldResolverGeneratorConfig($this->config),
            $this->containerCallGenerator,
        );

        $fieldResolverWithDirectiveGenerator = new Resolver\Wrapper\FieldResolverDirectiveWrapped(
            $this->serializer,
            $fieldGenerator,
            $defaultResolver,
            $directiveResolver,
        );

        $unionTypeResolver = new Resolver\Psr\Container\UnionTypeGenerator(
            new UnionResolveTypeGeneratorConfig($this->config),
            $this->containerCallGenerator
        );
        $scalarResolver = new Resolver\Psr\Container\ScalarGenerator(
            new ScalarResolverGeneratorConfig($this->config),
            $this->containerCallGenerator
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
            $scalarResolver,
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