<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Builder\Foundation\Psr\Container;

use Axtiva\FlexibleGraphql\Builder\TypeRegistryGeneratorBuilderInterface;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\Resolver;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\Resolver\Wrapper\FieldResolverAmphpAsyncWrapped;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\TypeRegistryGeneratorInterface;

class TypeRegistryGeneratorBuilderAmphp implements TypeRegistryGeneratorBuilderInterface
{
    private TypeRegistryGeneratorBuilderInterface $baseBuilder;

    public function __construct(TypeRegistryGeneratorBuilder $baseBuilder)
    {
        $this->baseBuilder = $baseBuilder;
        $this->baseBuilder->setFieldResolverGenerator(new FieldResolverAmphpAsyncWrapped($this->baseBuilder->getFieldResolverGenerator()));
    }

    public function build(): TypeRegistryGeneratorInterface
    {
        return $this->baseBuilder->build();
    }
}