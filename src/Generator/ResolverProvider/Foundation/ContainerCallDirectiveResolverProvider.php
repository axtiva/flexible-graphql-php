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
        return sprintf(<<<'PHP'
(function (callable $next, $directiveArgs, $rootValue, $args, $context, $info) {
    $resolver = $this->getService('%s');
    if (!\is_callable($resolver)) {
        throw new \RuntimeException('Directive resolver service is not callable: %s');
    }

    return $resolver($next, $directiveArgs, $rootValue, $args, $context, $info);
})
PHP, $config->getDirectiveResolverFullClassName($directive), $config->getDirectiveResolverFullClassName($directive));
    }
}
