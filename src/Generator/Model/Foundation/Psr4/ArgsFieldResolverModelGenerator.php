<?php

namespace Axtiva\FlexibleGraphql\Generator\Model\Foundation\Psr4;

use Axtiva\FlexibleGraphql\Generator\Config\ArgsFieldResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\EnumObjectGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\InputObjectGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\ScalarResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Exception\UnsupportedType;
use Axtiva\FlexibleGraphql\Generator\Model\ArgsFieldResolverModelGeneratorInterface;
use Axtiva\FlexibleGraphql\Resolver\TypedCustomScalarResolverInterface;
use Axtiva\FlexibleGraphql\Utils\ObjectHelper;
use Axtiva\FlexibleGraphql\Utils\TemplateRender;
use GraphQL\Type\Definition\BooleanType;
use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\FloatType;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\WrappingType;
use GraphQL\Type\Introspection;
use GraphQL\Type\Schema;

class ArgsFieldResolverModelGenerator implements ArgsFieldResolverModelGeneratorInterface
{
    private ArgsFieldResolverGeneratorConfigInterface $config;
    private ScalarResolverGeneratorConfigInterface $scalarConfig;
    private EnumObjectGeneratorConfigInterface $enumConfig;
    private InputObjectGeneratorConfigInterface $inputObjectConfig;

    public function __construct(
        ArgsFieldResolverGeneratorConfigInterface $config,
        ScalarResolverGeneratorConfigInterface $scalarConfig,
        EnumObjectGeneratorConfigInterface $enumConfig,
        InputObjectGeneratorConfigInterface $inputObjectConfig
    ) {
        $this->config = $config;
        $this->scalarConfig = $scalarConfig;
        $this->enumConfig = $enumConfig;
        $this->inputObjectConfig = $inputObjectConfig;
    }

    public function getConfig(): ArgsFieldResolverGeneratorConfigInterface
    {
        return $this->config;
    }

    public function isSupportedType(Type $type, FieldDefinition $field): bool
    {
        return !empty($field->args);
    }

    public function generate(Type $type, FieldDefinition $typeField, Schema $schema): string
    {
        if (false === $this->isSupportedType($type, $typeField)) {
            throw new UnsupportedType(sprintf('Unsupported type %s.%s for %s', $type->name, $typeField->name, __CLASS__));
        }

        $fields = [];
        $importClasses = [];
        foreach ($typeField->args as $field) {
            $fieldType = $this->getWrappedType($field->getType());

            if (
                (\in_array(\get_class($fieldType), [InputObjectType::class, EnumType::class, CustomScalarType::class]))
                && !Introspection::isIntrospectionType($fieldType)
            ) {
                if ($fieldType instanceof InputObjectType) {
                    $importClasses[] = $this->inputObjectConfig->getModelFullClassName($fieldType);
                } elseif ($fieldType instanceof EnumType) {
                    $importClasses[] = $this->enumConfig->getModelFullClassName($fieldType);
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
                'type' => $this->getFieldTypeDefinition($this->getWrappedType($field->getType())),
                'type_doc' => implode('|', $this->getFieldTypeDocDefinition($field->getType())),
                'is_custom' => $this->isCustomType($this->getWrappedType($field->getType())),
                'is_list' => $this->isListType($field->getType()),
                'list_level' => $this->getListLevel($field->getType()),
                'is_nullable' => !$field->getType() instanceof NonNull,
            ];
        }

        $template = __DIR__ . '/../../../../../templates/' . $this->config->getPHPVersion() . '/Model/FieldArgsModel.php';
        return TemplateRender::render($template, [
            'namespace' => $this->config->getFieldArgsNamespace($type, $typeField),
            'type_name' => $type->name,
            'field_name' => $typeField->name,
            'short_class_name' => $this->config->getFieldArgsClassName($type, $typeField),
            'type_description' => $typeField->description,
            'import_classes' => array_unique($importClasses),
            'fields' => $fields,
        ]);
    }

    private function isCustomType(Type $type): bool
    {
        if ($type instanceof NonNull) {
            $type = $type->getWrappedType();
        }

        return $type instanceof EnumType || $type instanceof InputObjectType;
    }

    private function isListType(Type $type): bool
    {
        if ($type instanceof NonNull) {
            $type = $type->getWrappedType();
        }

        return $type instanceof ListOfType;
    }

    private function getWrappedType(Type $type): Type
    {
        while($type instanceof WrappingType) {
            $type = $type->getWrappedType();
        }

        return $type;
    }

    private function getListLevel(Type $type): int
    {
        $level = 0;
        while($type instanceof WrappingType) {
            if ($type instanceof ListOfType) {
                $level++;
            }
            $type = $type->getWrappedType();
        }

        return $level;
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
            return $this->inputObjectConfig->getModelClassName($type);
        }

        throw new UnsupportedType($type->name);
    }

    private function getFieldTypeDocDefinition(Type $type): array
    {
        $types = [];
        if ($type instanceof NonNull) {
            $type = $type->getWrappedType();
            $types = array_merge($types, array_slice($this->getFieldTypeDocDefinition($type), 1));
        } else {
            $types[] = 'null';
            if ($type instanceof ListOfType) {
                $types[] = 'iterable';
                $types = array_merge($types, $this->getFieldTypeDocDefinition($type->getWrappedType()));
            } elseif ($type instanceof BooleanType) {
                $types[] = 'bool';
            } elseif ($type instanceof IntType) {
                $types[] = 'int';
            } elseif ($type instanceof FloatType) {
                $types[] = 'float';
            } elseif (
                $type instanceof IDType
                || $type instanceof StringType
            ) {
                $types[] = 'string';
            } elseif ($type instanceof CustomScalarType) {
                /** @var TypedCustomScalarResolverInterface|string $scalarClass */
                $scalarClass = $this->scalarConfig->getModelFullClassName($type);
                if (
                    \class_exists($scalarClass)
                    && \in_array(TypedCustomScalarResolverInterface::class, \class_implements($scalarClass) ?: [])
                ) {
                    $typeName = (string) $scalarClass::getTypeName();
                    if (! empty($typeName)) {
                        $types[] = ObjectHelper::getClassShortName($typeName);
                    }
                }
            } elseif ($type instanceof EnumType) {
                $types[] = $this->enumConfig->getModelClassName($type);
            } elseif ($type instanceof InputObjectType) {
                $types[] = $this->inputObjectConfig->getModelClassName($type);
            }
        }

        $types = array_unique($types);

        if (count($types) == 1 && $types[0] === 'null') {
            return [];
        }

        return $types;
    }
}