<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\Model\Foundation\Psr4;

use Axtiva\FlexibleGraphql\Generator\Config\FieldResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Exception\UnsupportedType;
use Axtiva\FlexibleGraphql\Generator\Model\FieldResolverGeneratorInterface;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class FieldResolverGenerator implements FieldResolverGeneratorInterface
{
    private FieldResolverGeneratorConfigInterface $config;

    public function __construct(FieldResolverGeneratorConfigInterface $config)
    {
        $this->config = $config;
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

        $loader = new FilesystemLoader(__DIR__ . '/../../../../../templates/' . $this->config->getPHPVersion());
        $twig = new Environment($loader);

        return $twig->render('Model/FieldResolver.php.twig', [
            'namespace' => $this->config->getFieldResolverNamespace($type, $field),
            'short_class_name' => $this->config->getFieldResolverClassName($type, $field),
            'field_description' => $field->description,
            'type_name' => $type->name,
            'field_name' => $field->name,
        ]);
    }
}