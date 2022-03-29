<?php

namespace Axtiva\FlexibleGraphql\Generator\Model\Foundation\Psr4;

use Axtiva\FlexibleGraphql\Generator\Config\ArgsDirectiveResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\EnumObjectGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\InputObjectGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\ScalarResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Exception\UnsupportedType;
use Axtiva\FlexibleGraphql\Generator\Model\ArgsDirectiveResolverModelGeneratorInterface;
use Axtiva\FlexibleGraphql\Resolver\TypedCustomScalarResolverInterface;
use Axtiva\FlexibleGraphql\Utils\ObjectHelper;
use GraphQL\Type\Definition\BooleanType;
use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\FloatType;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Introspection;
use GraphQL\Type\Schema;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ArgsDirectiveResolverModelGenerator implements ArgsDirectiveResolverModelGeneratorInterface
{
    private ArgsDirectiveResolverGeneratorConfigInterface $config;
    private ScalarResolverGeneratorConfigInterface $scalarConfig;
    private EnumObjectGeneratorConfigInterface $enumConfig;
    private InputObjectGeneratorConfigInterface $inputObjectConfig;

    public function __construct(
        ArgsDirectiveResolverGeneratorConfigInterface $config,
        ScalarResolverGeneratorConfigInterface $scalarConfig,
        EnumObjectGeneratorConfigInterface $enumConfig,
        InputObjectGeneratorConfigInterface $inputObjectConfig
    ) {
        $this->config = $config;
        $this->scalarConfig = $scalarConfig;
        $this->enumConfig = $enumConfig;
        $this->inputObjectConfig = $inputObjectConfig;
    }

    public function getConfig(): ArgsDirectiveResolverGeneratorConfigInterface
    {
        return $this->config;
    }

    public function isSupportedType(Directive $directive): bool
    {
        return !empty($directive->args);
    }

    public function generate(Directive $directive, Schema $schema): string
    {
        if (false === $this->isSupportedType($directive)) {
            throw new UnsupportedType(sprintf('Unsupported type %s for %s', $directive->name, __CLASS__));
        }

        $loader = new FilesystemLoader(__DIR__ . '/../../../../../templates/' . $this->config->getPHPVersion());
        $twig = new Environment($loader);

        $fields = [];
        $importClasses = [];
        foreach ($directive->args as $field) {
            $fieldType = $field->getType();
            if ($fieldType instanceof NonNull) {
                $fieldType = $fieldType->getWrappedType();
            }

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
                'type' => $this->getFieldTypePHPDefinition($field->getType()),
                'type_name' => $this->getFieldTypeDefinition($field->getType()),
                'is_custom' => $this->isCustomType($field->getType()),
                'is_nullable' => !$field->getType() instanceof NonNull,
            ];
        }

        return $twig->render('Model/DirectiveArgsModel.php.twig', [
            'namespace' => $this->config->getDirectiveArgsNamespace($directive),
            'type_name' => $directive->name,
            'short_class_name' => $this->config->getDirectiveArgsClassName($directive),
            'type_description' => $directive->description,
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

    private function getFieldTypePHPDefinition(Type $type): string
    {
        $nullSign = '?';
        if ($type instanceof NonNull) {
            $nullSign = '';
            $type = $type->getWrappedType();
        }

        $fieldDefinition = $this->getFieldTypeDefinition($type);

        return $fieldDefinition ? $nullSign . $fieldDefinition : '';
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
}