<?php

namespace Axtiva\FlexibleGraphql\Generator\Config;

use GraphQL\Type\Definition\Directive;

interface ArgsDirectiveResolverGeneratorConfigInterface
{
    public function getDirectiveArgsNamespace(Directive $directive): ?string;
    public function getDirectiveArgsClassName(Directive $directive): string;
    public function getDirectiveArgsFullClassName(Directive $directive): string;
    public function getDirectiveArgsClassFileName(Directive $directive): string;
    public function getDirectiveArgsDirPath(Directive $directive): string;
}