<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\Model\Foundation\Psr4;

use Axtiva\FlexibleGraphql\Generator\Config\EnumObjectGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\InputObjectGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\ScalarResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Exception\UnsupportedType;
use Axtiva\FlexibleGraphql\Generator\Model\InputObjectModelGeneratorInterface;
use Axtiva\FlexibleGraphql\Resolver\TypedCustomScalarResolverInterface;
use Axtiva\FlexibleGraphql\Utils\ObjectHelper;
use Axtiva\FlexibleGraphql\Utils\TemplateRender;
use GraphQL\Type\Definition\BooleanType;
use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Type\Definition\EnumType;
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
        $fields = [];
        $importClasses = [];
        foreach ($type->getFields() as $field) {
            $fieldType = $this->getWrappedType($field->getType());
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
                'type' => $this->getFieldTypeDefinition($this->getWrappedType($field->getType())),
                'type_doc' => implode('|', $this->getFieldTypeDocDefinition($field->getType())),
                'is_custom' => $this->isCustomType($this->getWrappedType($field->getType())),
                'is_list' => $this->isListType($field->getType()),
                'list_level' =>  $this->getListLevel($field->getType()),
                'is_nullable' => !$field->getType() instanceof NonNull,
            ];
        }

        $template = __DIR__ . '/../../../../../templates/' . $this->config->getPHPVersion() . '/Model/InputObjectModel.php';
        return TemplateRender::render($template, [
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
                $types[] = $this->config->getModelClassName($type);
            }
        }

        $types = array_unique($types);

        if (count($types) == 1 && $types[0] === 'null') {
            return [];
        }

        return $types;
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
}