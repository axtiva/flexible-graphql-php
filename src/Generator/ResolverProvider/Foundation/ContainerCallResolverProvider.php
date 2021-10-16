<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\ResolverProvider\Foundation;

use Axtiva\FlexibleGraphql\Generator\ResolverProvider\ResolverProviderInterface;

class ContainerCallResolverProvider implements ResolverProviderInterface
{
    public function generate(string $name): string
    {
        return sprintf('$this->container->get(\'%s\')', $name);
    }
}