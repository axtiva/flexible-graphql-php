<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\Model\Foundation\Psr4;

use Axtiva\FlexibleGraphql\Generator\Config\FieldResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Exception\UnsupportedType;
use Axtiva\FlexibleGraphql\Generator\Model\FieldResolverGeneratorInterface;
use Axtiva\FlexibleGraphql\Utils\TemplateRender;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;

class _ServiceResolverGenerator implements FieldResolverGeneratorInterface
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
        return $type->name === 'Query' && $field->name === '_service';
    }

    public function generate(Type $type, FieldDefinition $field, Schema $schema): string
    {
        if (false === $this->isSupportedType($type, $field)) {
            throw new UnsupportedType(sprintf(
                'Unsupported generator for %s.%s on %s',
                $type->name,
                $field->name,
                __CLASS__
            ));
        }

        /** @var ObjectType $type */

        $filename = $this->config->getFieldResolverClassFileName($type, $field);
        if (file_exists($filename)) {
            return file_get_contents($filename);
        }

        $template = __DIR__ . '/../../../../../templates/' . $this->config->getPHPVersion() . '/Model/_ServiceResolver.php';

        return TemplateRender::render($template, [
            'namespace' => $this->config->getFieldResolverNamespace($type, $field),
            'short_class_name' => $this->config->getFieldResolverClassName($type, $field),
        ]);
    }
}