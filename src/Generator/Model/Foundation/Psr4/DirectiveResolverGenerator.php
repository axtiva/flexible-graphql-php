<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\Model\Foundation\Psr4;

use Axtiva\FlexibleGraphql\Generator\Config\DirectiveResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\Exception\UnsupportedType;
use Axtiva\FlexibleGraphql\Generator\Model\DirectiveResolverGeneratorInterface;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Schema;
use PhpParser\BuilderFactory;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class DirectiveResolverGenerator implements DirectiveResolverGeneratorInterface
{
    private DirectiveResolverGeneratorConfigInterface $config;

    public function __construct(DirectiveResolverGeneratorConfigInterface $config)
    {
        $this->config = $config;
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

        $loader = new FilesystemLoader(__DIR__ . '/../../../../../templates');
        $twig = new Environment($loader);
        return $twig->render('Model/DirectiveResolver.php.twig', [
            'namespace' => $this->config->getDirectiveResolverNamespace($directive),
            'directive_description' => $directive->description,
            'directive_name' => $directive->name,
            'short_class_name' => $this->config->getDirectiveResolverClassName($directive),

        ]);
    }
}