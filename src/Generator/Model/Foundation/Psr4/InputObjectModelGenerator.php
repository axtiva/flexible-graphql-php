<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\Model\Foundation\Psr4;

use Axtiva\FlexibleGraphql\Generator\Config\EnumObjectGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\InputObjectGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\InterfaceGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\ObjectGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\ScalarResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\UnionObjectGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Exception\UnsupportedType;
use Axtiva\FlexibleGraphql\Generator\Model\InputObjectModelGeneratorInterface;
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

class InputObjectModelGenerator implements InputObjectModelGeneratorInterface
{
    private InputObjectGeneratorConfigInterface $config;
    private EnumObjectGeneratorConfigInterface $enumConfig;
    private ScalarResolverGeneratorConfigInterface $scalarConfig;

    public function __construct(
        InputObjectGeneratorConfigInterface $config,
        ScalarResolverGeneratorConfigInterface $scalarConfig,
        EnumObjectGeneratorConfigInterface $enumConfig
    ) {
        $this->config = $config;
        $this->enumConfig = $enumConfig;
        $this->scalarConfig = $scalarConfig;
    }

    public function getConfig(): InputObjectGeneratorConfigInterface
    {
        return $this->config;
    }

    public function isSupportedType(Type $type): bool
    {
        return $type instanceof InputObjectType && !Introspection::isIntrospectionType($type);
    }

    public function generate(Type $type, Schema $schema): string
    {
        if (false === $this->isSupportedType($type)) {
            throw new UnsupportedType(sprintf('Unsupported type %s for %s', $type->name, __CLASS__));
        }

        /** @var InputObjectType $type */
        $loader = new FilesystemLoader(__DIR__ . '/../../../../../templates/' . $this->config->getPHPVersion());
        $twig = new Environment($loader);

        $fields = [];
        $importClasses = [];
        foreach ($type->getFields() as $field) {
            $fieldType = $field->getType();
            if (
                (\in_array(\get_class($fieldType), [InputObjectType::class, EnumType::class, CustomScalarType::class]))
                && !Introspection::isIntrospectionType($fieldType)
            ) {
                if ($fieldType instanceof InputObjectType) {
                    if ($this->config->getModelNamespace($fieldType) !== $this->config->getModelNamespace($type)) {
                        $importClasses[] = $this->config->getModelFullClassName($fieldType);
                    }
                } elseif ($fieldType instanceof EnumType) {
                    if ($this->enumConfig->getModelNamespace($fieldType) !== $this->config->getModelNamespace($type)) {
                        $importClasses[] = $this->enumConfig->getModelFullClassName($fieldType);
                    }
                } elseif ($fieldType instanceof CustomScalarType) {
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
                'name' => $field->name,
                'description' => $field->description,
                'type' => $this->getFieldTypePHPDefinition($field->getType()),
                'type_name' => $this->getFieldTypeDefinition($field->getType()),
                'is_custom' => $this->isCustomType($field->getType()),
                'is_nullable' => !$field->getType() instanceof NonNull,
            ];
        }

        return $twig->render('Model/InputObjectModel.php.twig', [
            'namespace' => $this->config->getModelNamespace($type),
            'type_name' => $type->name,
            'short_class_name' => $this->config->getModelClassName($type),
            'type_description' => $type->description,
            'import_classes' => array_unique($importClasses),
            'fields' => $fields,
        ]);
    }

    private function isCustomType(Type $type): bool
    {
        if ($type instanceof NonNull) {
            $type = $type->getWrappedType();
        }

        return $type instanceof CustomScalarType || $type instanceof EnumType || $type instanceof InputObjectType;
    }

    private function getFieldTypePHPDefinition(Type $type): string
    {
        $nullSign = '?';
        if ($type instanceof NonNull) {
            $nullSign = '';
            $type = $type->getWrappedType();
        }

        return $nullSign . $this->getFieldTypeDefinition($type);
    }

    private function getFieldTypeDefinition(Type $type): string
    {
        if ($type instanceof NonNull) {
            $type = $type->getWrappedType();
            return $this->getFieldTypeDefinition($type);
        }

        if ($type instanceof ListOfType) {
            return 'iterable';
        } elseif ($type instanceof BooleanType) {
            return 'bool';
        } elseif ($type instanceof IntType) {
            return 'int';
        } elseif ($type instanceof FloatType) {
            return 'float';
        } elseif (
            $type instanceof IDType
            || $type instanceof StringType
        ) {
            return 'string';
        } elseif ($type instanceof CustomScalarType) {
            /** @var TypedCustomScalarResolverInterface|string $scalarClass */
            $scalarClass = $this->scalarConfig->getModelFullClassName($type);
            if (
                \class_exists($scalarClass)
                && \in_array(TypedCustomScalarResolverInterface::class, \class_implements($scalarClass) ?: [])
            ) {
                $typeName = (string) $scalarClass::getTypeName();
                if (! empty($typeName)) {
                    return ObjectHelper::getClassShortName($typeName);
                }
            }
            return '';
        } elseif ($type instanceof EnumType) {
            return $this->enumConfig->getModelClassName($type);
        } elseif ($type instanceof InputObjectType) {
            return $this->config->getModelClassName($type);
        }

        throw new UnsupportedType($type->name);
    }
}