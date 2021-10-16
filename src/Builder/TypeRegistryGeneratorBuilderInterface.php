<?php

namespace Axtiva\FlexibleGraphql\Builder;

use Axtiva\FlexibleGraphql\Generator\TypeRegistry\TypeRegistryGeneratorInterface;

interface TypeRegistryGeneratorBuilderInterface
{
    public function build(): TypeRegistryGeneratorInterface;
}