<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\Model\Foundation\Psr4;

use Axtiva\FlexibleGraphql\Generator\Config\ArgsFieldResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\EnumObjectGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\FieldResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\InterfaceGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\ObjectGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\ScalarResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\UnionObjectGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Exception\UnsupportedType;
use Axtiva\FlexibleGraphql\Generator\Model\FieldResolverGeneratorInterface;
use Axtiva\FlexibleGraphql\Resolver\TypedCustomScalarResolverInterface;
use Axtiva\FlexibleGraphql\Utils\ObjectHelper;
use Axtiva\FlexibleGraphql\Utils\TemplateRender;
use GraphQL\Type\Definition\BooleanType;
use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\FloatType;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType;
use GraphQL\Type\Schema;

class FieldResolverGenerator implements FieldResolverGeneratorInterface
{
    private FieldResolverGeneratorConfigInterface $config;
    private ArgsFieldResolverGeneratorConfigInterface $argsFieldConfig;
    private ObjectGeneratorConfigInterface $objectConfig;
    private ScalarResolverGeneratorConfigInterface $scalarConfig;
    private EnumObjectGeneratorConfigInterface $enumConfig;
    private UnionObjectGeneratorConfigInterface $unionConfig;
    private InterfaceGeneratorConfigInterface $interfaceConfig;

    public function __construct(
        FieldResolverGeneratorConfigInterface $config,
        ObjectGeneratorConfigInterface $objectConfig,
        ScalarResolverGeneratorConfigInterface $scalarConfig,
        EnumObjectGeneratorConfigInterface $enumConfig,
        UnionObjectGeneratorConfigInterface $unionConfig,
        InterfaceGeneratorConfigInterface $interfaceConfig,
        ArgsFieldResolverGeneratorConfigInterface $argsFieldConfig
    ) {
        $this->config = $config;
        $this->argsFieldConfig = $argsFieldConfig;
        $this->objectConfig = $objectConfig;
        $this->scalarConfig = $scalarConfig;
        $this->enumConfig = $enumConfig;
        $this->unionConfig = $unionConfig;
        $this->interfaceConfig = $interfaceConfig;
    }

    public function getConfig(): FieldResolverGeneratorConfigInterface
    {
        return $this->config;
    }

    public function isSupportedType(Type $type, FieldDefinition $field): bool
    {
        return true;
    }

    public function generate(Type $type, FieldDefinition $field, Schema $schema): string
    {
        if (false === $this->isSupportedType($type, $field)) {
            throw new UnsupportedType(sprintf('Unsupported field %s for %s', $field->name, __CLASS__));
        }

        $importClasses = [];
        $argsClass = null;
        $rootClass = null;

        if ($field->args) {
            $importClasses[] = $this->argsFieldConfig->getFieldArgsFullClassName($type, $field);
            $argsClass = $this->argsFieldConfig->getFieldArgsClassName($type, $field);
        }

        if (!\in_array($type->toString(), ['Query', 'Mutation', 'Subscribe'], true) ) {
            $importClasses[] = $this->objectConfig->getModelFullClassName($type);
            $rootClass = $this->objectConfig->getModelClassName($type);
        }

        [$returnClass, $returnFullClass] = $this->getFieldTypePHPDefinition($field->getType());
        if ($returnFullClass) {
            $importClasses[] = $returnFullClass;
        }

        $template = __DIR__ . '/../../../../../templates/' . $this->config->getPHPVersion() . '/Model/FieldResolver.php';
        return TemplateRender::render($template, [
            'namespace' => $this->config->getFieldResolverNamespace($type, $field),
            'short_class_name' => $this->config->getFieldResolverClassName($type, $field),
            'field_description' => $field->description,
            'root_value_class' => $rootClass,
            'field_args_class' => $argsClass,
            'return_class' => $returnClass,
            'type_name' => $type->toString(),
            'import_classes' => array_unique($importClasses),
            'field_name' => $field->name,
        ]);
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private function getFieldTypePHPDefinition(Type $type): array
    {
        $isNullable = !($type instanceof NonNull);

        [$fieldType, $fieldFullClassName] = $this->getFieldTypePHPBaseDefinition($type);
        if ($fieldType === null) {
            return [null, $fieldFullClassName];
        }

        return [$isNullable ? $fieldType . '|null' : $fieldType, $fieldFullClassName];
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private function getFieldTypePHPBaseDefinition(Type $type): array
    {
        if ($type instanceof NonNull) {
            return $this->getFieldTypePHPBaseDefinition($type->getWrappedType());
        }

        if ($type instanceof ListOfType) {
            [$wrappedShortName, $wrappedName] = $this->getFieldTypePHPBaseDefinition($type->getWrappedType());
            if ($wrappedShortName === null) {
                return [null, $wrappedName];
            }

            return ['iterable<int, ' . $wrappedShortName . '>', $wrappedName];
        } elseif ($type instanceof BooleanType) {
            return ['bool', null];
        } elseif ($type instanceof IntType) {
            return ['int', null];
        } elseif ($type instanceof FloatType) {
            return ['float', null];
        } elseif (
            $type instanceof IDType
            || $type instanceof StringType
        ) {
            return ['string', null];
        } elseif ($type instanceof CustomScalarType) {
            $scalarClass = $this->scalarConfig->getModelFullClassName($type);
            if (
                \class_exists($scalarClass)
                && \in_array(TypedCustomScalarResolverInterface::class, \class_implements($scalarClass) ?: [])
            ) {
                $typeName = (string) $scalarClass::getTypeName();
                if (! empty($typeName)) {
                    return [ObjectHelper::getClassShortName($typeName), $typeName];
                }
            }

            return [null, null];
        } elseif ($type instanceof UnionType) {
            return [$this->unionConfig->getModelClassName($type), $this->unionConfig->getModelFullClassName($type)];
        } elseif ($type instanceof InterfaceType) {
            return [$this->interfaceConfig->getModelClassName($type), $this->interfaceConfig->getModelFullClassName($type)];
        } elseif ($type instanceof EnumType) {
            return [$this->enumConfig->getModelClassName($type), $this->enumConfig->getModelFullClassName($type)];
        } elseif ($type instanceof ObjectType) {
            return [$this->objectConfig->getModelClassName($type), $this->objectConfig->getModelFullClassName($type)];
        }

        throw new UnsupportedType($type->toString());
    }
}
