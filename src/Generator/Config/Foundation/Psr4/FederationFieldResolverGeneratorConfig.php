<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4;

use Axtiva\FlexibleGraphql\Generator\Config\CodeGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\FieldResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\GetPHPVersionFromCodeGeneratorTrait;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;

class FederationFieldResolverGeneratorConfig extends FieldResolverGeneratorConfig
{
    use GetPHPVersionFromCodeGeneratorTrait;

    public function getFieldResolverClassName(Type $type, FieldDefinition $field): string
    {
        // Fix federation field resolver name
        if (in_array($field->getName(), ['_entities', '_service'])) {
            return '_' . ucfirst(substr($field->getName(), 1)) . 'Resolver';
        }
        return ucfirst($field->getName()) . 'Resolver';
    }
}