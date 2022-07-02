<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\Model\Foundation\Psr4;

use Axtiva\FlexibleGraphql\Generator\Config\UnionObjectGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Exception\UnsupportedType;
use Axtiva\FlexibleGraphql\Generator\Model\ModelGeneratorInterface;
use Axtiva\FlexibleGraphql\Utils\TemplateRender;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType;
use GraphQL\Type\Schema;

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

        $template = __DIR__ . '/../../../../../templates/' . $this->config->getPHPVersion() . '/Model/UnionModel.php';
        return TemplateRender::render($template, [
            'namespace' => $this->config->getModelNamespace($type),
            'description' => $type->description,
            'short_class_name' => $this->config->getModelClassName($type),
        ]);
    }
}