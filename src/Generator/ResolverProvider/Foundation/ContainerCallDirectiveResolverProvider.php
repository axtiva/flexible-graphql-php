<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\ResolverProvider\Foundation;

use Axtiva\FlexibleGraphql\Generator\Config\DirectiveResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\ResolverProvider\DirectiveResolverProviderInterface;
use GraphQL\Type\Definition\Directive;

class ContainerCallDirectiveResolverProvider implements DirectiveResolverProviderInterface
{
    public function generate(DirectiveResolverGeneratorConfigInterface $config, Directive $directive): string
    {
        return sprintf('$this->container->get(\'%s\')', $config->getDirectiveResolverFullClassName($directive));
    }
}