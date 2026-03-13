<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\ResolverProvider\Foundation;

use Axtiva\FlexibleGraphql\Generator\Config\ScalarResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\ResolverProvider\ScalarResolverProviderInterface;
use GraphQL\Type\Definition\Type;

class ContainerCallScalarResolverProvider implements ScalarResolverProviderInterface
{
    public function generate(ScalarResolverGeneratorConfigInterface $config, Type $type): string
    {
        return sprintf(<<<'PHP'
(function () {
    return $this->getService('%s');
})()
PHP, $config->getModelFullClassName($type));
    }
}
