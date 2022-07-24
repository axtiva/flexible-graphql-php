<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Builder\Foundation\Psr\Container;

use Axtiva\FlexibleGraphql\Generator\Config\CodeGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\FederationArgsFieldResolverGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\FederationFieldResolverGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\Resolver;

class TypeRegistryGeneratorBuilderFederated extends TypeRegistryGeneratorBuilder
{
    public function __construct(CodeGeneratorConfigInterface $config)
    {
        parent::__construct($config);
        $this->setFieldResolverGeneratorConfig(new FederationFieldResolverGeneratorConfig($config));
        $this->setArgsFieldResolveGeneratorConfig(new FederationArgsFieldResolverGeneratorConfig($config));
    }
}