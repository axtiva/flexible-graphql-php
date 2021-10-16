<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\Model\Foundation\Psr4;

use Axtiva\FlexibleGraphql\Generator\Config\ObjectGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\UnionResolveTypeGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Exception\UnsupportedType;
use Axtiva\FlexibleGraphql\Generator\Model\ObjectModelGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\Model\UnionResolveTypeModelGeneratorInterface;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType;
use GraphQL\Type\Schema;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class UnionResolveTypeModelGenerator implements UnionResolveTypeModelGeneratorInterface
{
    private ObjectModelGeneratorInterface $modelGenerator;
    private UnionResolveTypeGeneratorConfigInterface $config;

    public function __construct(
        UnionResolveTypeGeneratorConfigInterface $config,
        ObjectModelGeneratorInterface $modelGenerator
    ) {
        $this->modelGenerator = $modelGenerator;
        $this->config = $config;
    }

    public function getConfig(): UnionResolveTypeGeneratorConfigInterface
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
        /** @var UnionType $type */

        $loader = new FilesystemLoader(__DIR__ . '/../../../../../templates');
        $twig = new Environment($loader);

        $classesInUse = [];
        $models = [];
        /** @var ObjectGeneratorConfigInterface $config */
        $config = $this->modelGenerator->getConfig();
        foreach ($type->getTypes() as $unionType) {
            $classesInUse[] = $config->getModelFullClassName($unionType);
            $models[] = [
                'model' => $config->getModelClassName($unionType),
                'type' => $unionType->toString(),
            ];
        }

        return $twig->render('Model/UnionResolveTypeModel.php.twig', [
            'namespace' => $this->config->getModelNamespace($type),
            'description' => $type->description,
            'short_class_name' => $this->config->getModelClassName($type),
            'models' => $models,
            'uses' => $classesInUse,
        ]);
    }
}