<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\Config\Foundation\Psr4;

use Axtiva\FlexibleGraphql\Generator\Config\ArgsDirectiveResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\CodeGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\Foundation\GetPHPVersionFromCodeGeneratorTrait;
use GraphQL\Type\Definition\Directive;

class ArgsDirectiveResolverGeneratorConfig implements ArgsDirectiveResolverGeneratorConfigInterface
{
    use GetPHPVersionFromCodeGeneratorTrait;

    private CodeGeneratorConfigInterface $config;

    public function __construct(CodeGeneratorConfigInterface $config)
    {
        $this->config = $config;
    }

    public function getDirectiveArgsNamespace(Directive $directive): ?string
    {
        return $this->config->getCodeNamespace() . "\\DirectiveArgs";
    }

    public function getDirectiveArgsClassName(Directive $directive): string
    {
        return ucfirst($directive->name) . 'DirectiveArgs';
    }

    public function getDirectiveArgsFullClassName(Directive $directive): string
    {
        return $this->getDirectiveArgsNamespace($directive)
            ? $this->getDirectiveArgsNamespace($directive) . '\\' . $this->getDirectiveArgsClassName($directive)
            : $this->getDirectiveArgsClassName($directive);
    }

    public function getDirectiveArgsClassFileName(Directive $directive): string
    {
        return $this->getDirectiveArgsDirPath($directive) . \DIRECTORY_SEPARATOR . "{$this->getDirectiveArgsClassName($directive)}.php";
    }

    public function getDirectiveArgsDirPath(Directive $directive): string
    {
        return $this->config->getCodeDirPath() . \DIRECTORY_SEPARATOR . 'DirectiveArgs';
    }
}