<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\Model\Foundation\Psr4;

use Axtiva\FlexibleGraphql\Generator\Config\EnumObjectGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\InterfaceGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\ObjectGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\ScalarResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\UnionObjectGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Exception\UnsupportedType;
use Axtiva\FlexibleGraphql\Generator\Model\ObjectModelGeneratorInterface;
use Axtiva\FlexibleGraphql\Resolver\AutoGenerationInterface;
use Axtiva\FlexibleGraphql\Resolver\TypedCustomScalarResolverInterface;
use Axtiva\FlexibleGraphql\Utils\ObjectHelper;
use GraphQL\Type\Definition\BooleanType;
use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\FloatType;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType;
use GraphQL\Type\Introspection;
use GraphQL\Type\Schema;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ObjectModelGenerator implements ObjectModelGeneratorInterface
{
    private ObjectGeneratorConfigInterface $config;
    private UnionObjectGeneratorConfigInterface $unionConfig;
    private InterfaceGeneratorConfigInterface $interfaceConfig;
    private EnumObjectGeneratorConfigInterface $enumConfig;
    private ScalarResolverGeneratorConfigInterface $scalarConfig;

    public function __construct(
        ObjectGeneratorConfigInterface $config,
        UnionObjectGeneratorConfigInterface $unionConfig,
        ScalarResolverGeneratorConfigInterface $scalarConfig,
        InterfaceGeneratorConfigInterface $interfaceConfig,
        EnumObjectGeneratorConfigInterface $enumConfig
    ) {
        $this->config = $config;
        $this->unionConfig = $unionConfig;
        $this->interfaceConfig = $interfaceConfig;
        $this->enumConfig = $enumConfig;
        $this->scalarConfig = $scalarConfig;
    }

    public function getConfig(): ObjectGeneratorConfigInterface
    {
        return $this->config;
    }

    public function isSupportedType(Type $type): bool
    {
        return $type instanceof ObjectType && !Introspection::isIntrospectionType($type);
    }

    public function generate(Type $type, Schema $schema): string
    {
        if (false === $this->isSupportedType($type)) {
            throw new UnsupportedType(sprintf('Unsupported type %s for %s', $type->name, __CLASS__));
        }

        /** @var ObjectType $type */

        $loader = new FilesystemLoader(__DIR__ . '/../../../../../templates/' . $this->config->getPHPVersion());
        $twig = new Environment($loader);

        $implements = [ObjectHelper::getClassShortName(AutoGenerationInterface::class)];
        foreach ($type->getInterfaces() as $implementedInterface) {
            $implements[] = $this->interfaceConfig->getModelClassName($implementedInterface);
        }

        foreach ($schema->getTypeMap() as $schemaType) {
            if ($schemaType instanceof UnionType && $schemaType->isPossibleType($type)) {
                $implements[] = $this->unionConfig->getModelClassName($schemaType);
            }
        }

        $fields = [];
        $importClasses = [];
        foreach ($type->getFields() as $field) {
            $fieldType = $field->getType();
            if ($fieldType instanceof NonNull) {
                $fieldType = $fieldType->getWrappedType();
            }
            if (
                (\in_array(\get_class($fieldType), [CustomScalarType::class]))
                && !Introspection::isIntrospectionType($fieldType)
            ) {
                if ($fieldType instanceof CustomScalarType) {
                    /** @var TypedCustomScalarResolverInterface|string $scalarClass */
                    $scalarClass = $this->scalarConfig->getModelFullClassName($fieldType);
                    if (
                        \class_exists($scalarClass)
                        && \in_array(TypedCustomScalarResolverInterface::class, \class_implements($scalarClass) ?: [])
                    ) {
                        $typeName = (string) $scalarClass::getTypeName();
                        if (! empty($typeName)) {
                            $importClasses[] = $typeName;
                        }
                    }
                } else {
                    throw new UnsupportedType($fieldType->name);
                }
            }

            $fields[] =  [
                'name' => $field->getName(),
                'description' => $field->description,
                'deprecation' => $field->deprecationReason,
                'type' => $this->getFieldTypePHPDefinition($field->getType()),
                'is_nullable' => !$field->getType() instanceof NonNull,
            ];
        }

        return $twig->render('Model/ObjectModel.php.twig', [
            'namespace' => $this->config->getModelNamespace($type),
            'type_name' => $type->name,
            'short_class_name' => $this->config->getModelClassName($type),
            'type_description' => $type->description,
            'implements' => $implements,
            'import_classes' => array_unique($importClasses),
            'fields' => $fields,
        ]);
    }

    private function getFieldTypePHPDefinition(Type $type): string
    {
        $nullSign = '?';
        if ($type instanceof NonNull) {
            $nullSign = '';
            $type = $type->getWrappedType();
        }

        if ($type instanceof ListOfType) {
            return $nullSign . 'iterable';
        } elseif ($type instanceof BooleanType) {
            return $nullSign . 'bool';
        } elseif ($type instanceof IntType) {
            return $nullSign . 'int';
        } elseif ($type instanceof FloatType) {
            return $nullSign . 'float';
        } elseif (
            $type instanceof IDType
            || $type instanceof StringType
        ) {
            return $nullSign . 'string';
        } elseif ($type instanceof CustomScalarType) {
            /** @var TypedCustomScalarResolverInterface|string $scalarClass */
            $scalarClass = $this->scalarConfig->getModelFullClassName($type);
            if (
                \class_exists($scalarClass)
                && \in_array(TypedCustomScalarResolverInterface::class, \class_implements($scalarClass) ?: [])
            ) {
                $typeName = (string) $scalarClass::getTypeName();
                if (! empty($typeName)) {
                    return $nullSign . ObjectHelper::getClassShortName($typeName);
                }
            }

            return '';
        } elseif ($type instanceof UnionType) {
            return $nullSign . $this->unionConfig->getModelClassName($type);
        } elseif ($type instanceof InterfaceType) {
            return $nullSign . $this->interfaceConfig->getModelClassName($type);
        } elseif ($type instanceof EnumType) {
            return $nullSign . $this->enumConfig->getModelClassName($type);
        } elseif ($type instanceof ObjectType) {
            return $nullSign . $this->config->getModelClassName($type);
        }

        throw new UnsupportedType($type->name);
    }
}