<?php

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry;


use GraphQL\Type\Definition\Directive;

interface DirectiveRegistryMethodGeneratorInterface
{
    public function getMethodCall(Directive $directive): string;
    public function getMethod(Directive $directive): string;
}