<?php

namespace Axtiva\FlexibleGraphql\Builder\Foundation;

use Axtiva\FlexibleGraphql\Generator\Config\CodeGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\EnumModelGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\FederationArgsFieldResolverGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\FederationFieldResolverGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\FederationRepresentationResolverGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\InputObjectGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4\ScalarResolverGeneratorConfig;
use Axtiva\FlexibleGraphql\Generator\Model\Foundation\Psr4\_EntitiesResolverGenerator;
use Axtiva\FlexibleGraphql\Generator\Model\Foundation\Psr4\_ServiceResolverGenerator;
use Axtiva\FlexibleGraphql\Generator\Model\Foundation\Psr4\ArgsFieldResolverModelGenerator;
use Axtiva\FlexibleGraphql\Generator\Model\Foundation\Psr4\FederationRepresentationResolverGenerator;

class CodeGeneratorBuilderFederated extends CodeGeneratorBuilder
{
    public function __construct(CodeGeneratorConfigInterface $config)
    {
        parent::__construct($config);
        $federatedConfig = new FederationFieldResolverGeneratorConfig($config);
        $this->addFieldResolverGenerator(new _EntitiesResolverGenerator($federatedConfig));
        $this->addFieldResolverGenerator(new _ServiceResolverGenerator($federatedConfig));
        $this->setArgsFieldResolverGenerator(new ArgsFieldResolverModelGenerator(
            new FederationArgsFieldResolverGeneratorConfig($config),
            new ScalarResolverGeneratorConfig($config),
            new EnumModelGeneratorConfig($config),
            new InputObjectGeneratorConfig($config),
        ));

        $this->addModelGenerator(
            new FederationRepresentationResolverGenerator(
                new FederationRepresentationResolverGeneratorConfig($config)
            )
        );
    }
}