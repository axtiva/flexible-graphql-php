<?php

namespace Axtiva\FlexibleGraphql\Generator\Config;

use GraphQL\Type\Definition\Directive;

interface DirectiveResolverGeneratorConfigInterface extends LanguageLevelConfigInterface
{
    public function getDirectiveResolverNamespace(Directive $directive): ?string;
    public function getDirectiveResolverClassName(Directive $directive): string;
    public function getDirectiveResolverFullClassName(Directive $directive): string;
    public function getDirectiveResolverClassFileName(Directive $directive): string;
    public function getDirectiveResolverDirPath(Directive $directive): string;
}