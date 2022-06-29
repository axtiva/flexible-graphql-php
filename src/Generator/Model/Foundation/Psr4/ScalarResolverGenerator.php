<?php

namespace Axtiva\FlexibleGraphql\Generator\Model\Foundation\Psr4;

use Axtiva\FlexibleGraphql\Generator\Config\ModelGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\ScalarResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Exception\UnsupportedType;
use Axtiva\FlexibleGraphql\Generator\Model\ScalarResolverGeneratorInterface;
use Axtiva\FlexibleGraphql\Utils\TemplateRender;
use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ScalarResolverGenerator implements ScalarResolverGeneratorInterface
{
    private ScalarResolverGeneratorConfigInterface $config;

    public function __construct(ScalarResolverGeneratorConfigInterface $config)
    {
        $this->config = $config;
    }

    public function getConfig(): ModelGeneratorConfigInterface
    {
        return $this->config;
    }

    public function isSupportedType(Type $type): bool
    {
        return $type instanceof CustomScalarType;
    }

    public function generate(Type $type, Schema $schema): string
    {
        if (false === $this->isSupportedType($type)) {
            throw new UnsupportedType(sprintf('Unsupported type %s in %s', $type->name, __CLASS__));
        }
        /** @var CustomScalarType $type */
        $template = __DIR__ . '/../../../../../templates/' . $this->config->getPHPVersion() . '/Model/ScalarResolver.php';
        return TemplateRender::render($template, [
            'namespace' => $this->config->getModelNamespace($type),
            'short_class_name' => $this->config->getModelClassName($type),
            'description' => $type->description,
            'type_name' => $type->name,
        ]);
    }
}