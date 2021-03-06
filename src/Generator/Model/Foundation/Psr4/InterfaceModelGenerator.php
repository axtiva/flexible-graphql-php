<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\Model\Foundation\Psr4;

use Axtiva\FlexibleGraphql\Generator\Config\InterfaceGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Exception\UnsupportedType;
use Axtiva\FlexibleGraphql\Generator\Model\InterfaceModelGeneratorInterface;
use Axtiva\FlexibleGraphql\Utils\TemplateRender;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;

class InterfaceModelGenerator implements InterfaceModelGeneratorInterface
{
    private InterfaceGeneratorConfigInterface $config;

    public function __construct(InterfaceGeneratorConfigInterface $config)
    {
        $this->config = $config;
    }

    public function getConfig(): InterfaceGeneratorConfigInterface
    {
        return $this->config;
    }

    public function isSupportedType(Type $type): bool
    {
        return $type instanceof InterfaceType;
    }

    public function generate(Type $type, Schema $schema): string
    {
        if (false === $this->isSupportedType($type)) {
            throw new UnsupportedType(sprintf('Unsupported type %s for %s', $type->name, __CLASS__));
        }

        $template = __DIR__ . '/../../../../../templates/' . $this->config->getPHPVersion() . '/Model/InterfaceModel.php';
        return TemplateRender::render($template, [
            'namespace' => $this->config->getModelNamespace($type),
            'short_class_name' => $this->config->getModelClassName($type),
            'interface_name' => $type->name,
            'interface_description' => $type->description,
            'implements' => array_map(
                fn($element) => $this->config->getModelClassName($element),
                $type->getInterfaces()
            ),
        ]);
    }
}