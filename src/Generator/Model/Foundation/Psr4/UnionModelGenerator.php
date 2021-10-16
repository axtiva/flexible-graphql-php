<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\Model\Foundation\Psr4;

use Axtiva\FlexibleGraphql\Generator\Config\UnionObjectGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Exception\UnsupportedType;
use Axtiva\FlexibleGraphql\Generator\Model\ModelGeneratorInterface;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType;
use GraphQL\Type\Schema;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class UnionModelGenerator implements ModelGeneratorInterface
{
    private UnionObjectGeneratorConfigInterface $config;

    public function __construct(UnionObjectGeneratorConfigInterface $config)
    {
        $this->config = $config;
    }

    public function getConfig(): UnionObjectGeneratorConfigInterface
    {
        return $this->config;
    }

    public function isSupportedType(Type $type): bool
    {
        return $type instanceof UnionType;
    }

    public function generate(Type $type, Schema $schema): string
    {
        if (false === $this->isSupportedType($type)) {
            throw new UnsupportedType(sprintf('Unsupported type %s for %s', $type->name, __CLASS__));
        }

        $loader = new FilesystemLoader(__DIR__ . '/../../../../../templates');
        $twig = new Environment($loader);

        return $twig->render('Model/UnionModel.php.twig', [
            'namespace' => $this->config->getModelNamespace($type),
            'description' => $type->description,
            'short_class_name' => $this->config->getModelClassName($type),
        ]);
    }
}