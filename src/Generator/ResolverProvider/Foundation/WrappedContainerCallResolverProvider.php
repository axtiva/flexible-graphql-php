<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\ResolverProvider\Foundation;

use Axtiva\FlexibleGraphql\Generator\ResolverProvider\ResolverProviderInterface;

class WrappedContainerCallResolverProvider implements ResolverProviderInterface
{
    private ResolverProviderInterface $generator;

    public function __construct(ResolverProviderInterface $generator)
    {
        $this->generator = $generator;
    }

    public function generate(string $name): string
    {
        return sprintf(<<<'PHP'
(function ($rootValue, $args, $context, $info) {
    return %s($rootValue, $args, $context, $info);
})
PHP, $this->generator->generate($name));
    }
}