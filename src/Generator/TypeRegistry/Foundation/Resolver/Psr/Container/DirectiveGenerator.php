<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\Resolver\Psr\Container;

use Axtiva\FlexibleGraphql\Generator\Config\DirectiveResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\ResolverProvider\ResolverProviderInterface;
use GraphQL\Language\AST\DirectiveNode;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\DirectiveResolverGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\Exception\NotDefinedResolver;
use GraphQL\Type\Definition\Directive;

class DirectiveGenerator implements DirectiveResolverGeneratorInterface
{
    private DirectiveResolverGeneratorConfigInterface $directiveConfig;
    private ResolverProviderInterface $resolverProvider;

    public function __construct(
        DirectiveResolverGeneratorConfigInterface $directiveConfig,
        ResolverProviderInterface $resolverProvider
    ) {
        $this->directiveConfig = $directiveConfig;
        $this->resolverProvider = $resolverProvider;
    }

    public function hasResolver(DirectiveNode $directive): bool
    {
        return file_exists($this->directiveConfig->getDirectiveResolverClassFileName(
            $this->getDirectiveDefinition($directive))
        );
    }

    public function generate(DirectiveNode $directive): string
    {
        if ($this->hasResolver($directive)) {
            $directiveDefinition = $this->getDirectiveDefinition($directive);
            $namespace = $this->directiveConfig->getDirectiveResolverNamespace($directiveDefinition)
                ?  $this->directiveConfig->getDirectiveResolverNamespace($directiveDefinition) . '\\'
                : '';
            return $this->resolverProvider->generate(
                $namespace . $this->directiveConfig->getDirectiveResolverClassName($directiveDefinition)
            );
        }

        throw new NotDefinedResolver($directive->name->value);
    }

    private function getDirectiveDefinition(DirectiveNode $directive): Directive
    {
        return new Directive([
            'name' => $directive->name->value,
            'locations' => [],
        ]);
    }
}