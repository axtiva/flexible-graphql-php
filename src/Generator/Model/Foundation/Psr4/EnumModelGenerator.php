<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\Model\Foundation\Psr4;

use Axtiva\FlexibleGraphql\Generator\Config\EnumObjectGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Exception\UnsupportedType;
use Axtiva\FlexibleGraphql\Generator\Model\EnumModelGeneratorInterface;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Introspection;
use GraphQL\Type\Schema;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class EnumModelGenerator implements EnumModelGeneratorInterface
{
    private EnumObjectGeneratorConfigInterface $config;

    public function __construct(EnumObjectGeneratorConfigInterface $config)
    {
        $this->config = $config;
    }

    public function getConfig(): EnumObjectGeneratorConfigInterface
    {
        return $this->config;
    }

    public function isSupportedType(Type $type): bool
    {
        return $type instanceof EnumType && !Introspection::isIntrospectionType($type);
    }

    public function generate(Type $type, Schema $schema): string
    {
        /** @var EnumType $type */
        if (false === $this->isSupportedType($type)) {
            throw new UnsupportedType(sprintf('Unsupported type %s for %s', $type->name, __CLASS__));
        }

        $loader = new FilesystemLoader(__DIR__ . '/../../../../../templates');
        $twig = new Environment($loader);

        return $twig->render('Model/EnumModel.php.twig', [
            'namespace' => $this->config->getModelNamespace($type),
            'short_class_name' => $this->config->getModelClassName($type),
            'enum_description' => $type->description,
            'enums' => array_map(
                fn($enum) => ['value' => $enum->value, 'description' => $enum->description],
                $type->getValues()
            ),
        ]);
    }
}