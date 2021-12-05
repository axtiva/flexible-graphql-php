<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\ResolverProvider\Foundation;

use Axtiva\FlexibleGraphql\Generator\ResolverProvider\ResolverProviderInterface;

class WrappedContainerCallResolverProvider implements ResolverProviderInterface
{
    public function generate(string $name): string
    {
        return sprintf(<<<'PHP'
function ($rootValue, $args, $context, $info) {
    $this->container->get('%s')($rootValue, $args, $context, $info);
}
PHP, $name);
    }
}