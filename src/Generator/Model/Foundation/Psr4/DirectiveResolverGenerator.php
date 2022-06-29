<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\Model\Foundation\Psr4;

use Axtiva\FlexibleGraphql\Generator\Config\ArgsDirectiveResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Config\DirectiveResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Exception\UnsupportedType;
use Axtiva\FlexibleGraphql\Generator\Model\DirectiveResolverGeneratorInterface;
use Axtiva\FlexibleGraphql\Utils\TemplateRender;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Schema;

class DirectiveResolverGenerator implements DirectiveResolverGeneratorInterface
{
    private DirectiveResolverGeneratorConfigInterface $config;
    private ArgsDirectiveResolverGeneratorConfigInterface $argsDirectiveConfig;

    public function __construct(
        DirectiveResolverGeneratorConfigInterface $config,
        ArgsDirectiveResolverGeneratorConfigInterface $argsDirectiveConfig
    ) {
        $this->config = $config;
        $this->argsDirectiveConfig = $argsDirectiveConfig;
    }

    public function getConfig(): DirectiveResolverGeneratorConfigInterface
    {
        return $this->config;
    }

    public function isSupportedType(Directive $directive): bool
    {
        return true;
    }

    public function generate(Directive $directive, Schema $schema): string
    {
        if (false === $this->isSupportedType($directive)) {
            throw new UnsupportedType(sprintf('Unsupported directive %s for %s', $directive->name, __CLASS__));
        }

        $importClasses = [];
        $argsClass = null;
        if ($directive->args) {
            $importClasses[] = $this->argsDirectiveConfig->getDirectiveArgsFullClassName($directive);
            $argsClass = $this->argsDirectiveConfig->getDirectiveArgsClassName($directive);
        }

        $template = __DIR__ . '/../../../../../templates/' . $this->config->getPHPVersion() . '/Model/DirectiveResolver.php';
        return TemplateRender::render($template, [
            'namespace' => $this->config->getDirectiveResolverNamespace($directive),
            'directive_description' => $directive->description,
            'directive_name' => $directive->name,
            'directive_args_class' => $argsClass,
            'import_classes' => array_unique($importClasses),
            'short_class_name' => $this->config->getDirectiveResolverClassName($directive),
        ]);
    }
}