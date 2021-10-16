<?php

namespace Axtiva\FlexibleGraphql\Generator\ResolverProvider;

interface ResolverProviderInterface
{
    public function generate(string $name): string;
}