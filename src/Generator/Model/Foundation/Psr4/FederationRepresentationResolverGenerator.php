<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\Model\Foundation\Psr4;

use Axtiva\FlexibleGraphql\Generator\Config\FederationRepresentationResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Exception\UnsupportedType;
use Axtiva\FlexibleGraphql\Generator\Model\FederationRepresentationResolverGeneratorInterface;
use Axtiva\FlexibleGraphql\Utils\TemplateRender;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;

class FederationRepresentationResolverGenerator implements FederationRepresentationResolverGeneratorInterface
{
    private FederationRepresentationResolverGeneratorConfigInterface $config;

    public function __construct(FederationRepresentationResolverGeneratorConfigInterface $config)
    {
        $this->config = $config;
    }

    public function getConfig(): FederationRepresentationResolverGeneratorConfigInterface
    {
        return $this->config;
    }

    public function isSupportedType(Type $type): bool
    {
        if ($type instanceof ObjectType && $type->astNode !== null) {
            foreach ($type->astNode->directives as $node) {
                if ($node->name->kind === 'Name' && $node->name->value === 'key') {
                    return true;
                }
            }
        }

        return false;
    }

    public function generate(Type $type, Schema $schema): string
    {
        if (false === $this->isSupportedType($type)) {
            throw new UnsupportedType(sprintf('Unsupported type %s for %s', $type->name, __CLASS__));
        }

        /** @var ObjectType $type */

        $filename = $this->config->getModelClassFileName($type);
        if (file_exists($filename)) {
            return file_get_contents($filename);
        }

        $template = __DIR__ . '/../../../../../templates/' . $this->config->getPHPVersion() . '/Model/RepresentationResolver.php';

        return TemplateRender::render($template, [
            'namespace' => $this->config->getModelNamespace($type),
            'type_name' => $type->name,
            'short_class_name' => $this->config->getModelClassName($type),
        ]);
    }
}