<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4;

use Axtiva\FlexibleGraphql\Generator\Config\CodeGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\DirectiveResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\GetPHPVersionFromCodeGeneratorTrait;
use GraphQL\Type\Definition\Directive;

class DirectiveResolverGeneratorConfig implements DirectiveResolverGeneratorConfigInterface
{
    use GetPHPVersionFromCodeGeneratorTrait;

    private CodeGeneratorConfigInterface $config;

    public function __construct(CodeGeneratorConfigInterface $config)
    {
        $this->config = $config;
    }

    public function getDirectiveResolverNamespace(Directive $directive): ?string
    {
        return $this->config->getCodeNamespace() . "\\Directive";
    }

    public function getDirectiveResolverClassName(Directive $directive): string
    {
        return ucfirst($directive->name) . 'Directive';
    }

    public function getDirectiveResolverFullClassName(Directive $directive): string
    {
        return $this->getDirectiveResolverNamespace($directive)
            ? $this->getDirectiveResolverNamespace($directive) . '\\' . $this->getDirectiveResolverClassName($directive)
            : $this->getDirectiveResolverClassName($directive);
    }

    public function getDirectiveResolverClassFileName(Directive $directive): string
    {
        return $this->getDirectiveResolverDirPath($directive) . \DIRECTORY_SEPARATOR . "{$this->getDirectiveResolverClassName($directive)}.php";
    }

    public function getDirectiveResolverDirPath(Directive $directive): string
    {
        return $this->config->getCodeDirPath() . \DIRECTORY_SEPARATOR . 'Directive';
    }
}