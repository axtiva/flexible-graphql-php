<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4;

use Axtiva\FlexibleGraphql\Generator\Config\ArgsFieldResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\CodeGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\GetPHPVersionFromCodeGeneratorTrait;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;

class FederationArgsFieldResolverGeneratorConfig extends ArgsFieldResolverGeneratorConfig
{
    use GetPHPVersionFromCodeGeneratorTrait;

    public function getFieldArgsClassName(Type $type, FieldDefinition $field): string
    {
        // Fix federation field resolver name
        if ($field->getName() === '_entities') {
            return '_' . ucfirst(substr($field->getName(), 1)) . 'ResolverArgs';
        }

        return ucfirst($field->getName()) . 'ResolverArgs';
    }
}