<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation\Resolver\Psr\Container;

use Axtiva\FlexibleGraphql\Generator\Config\DirectiveResolverGeneratorConfigInterface;
use Axtiva\FlexibleGraphql\Generator\ResolverProvider\DirectiveResolverProviderInterface;
use Axtiva\FlexibleGraphql\Generator\ResolverProvider\FieldResolverProviderInterface;
use GraphQL\Language\AST\DirectiveNode;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\DirectiveResolverGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\Exception\NotDefinedResolver;
use GraphQL\Type\Definition\Directive;

class DirectiveGenerator implements DirectiveResolverGeneratorInterface
{
    private DirectiveResolverGeneratorConfigInterface $directiveConfig;
    private DirectiveResolverProviderInterface $resolverProvider;

    public function __construct(
        DirectiveResolverGeneratorConfigInterface $directiveConfig,
        DirectiveResolverProviderInterface $resolverProvider
    ) {
        $this->directiveConfig = $directiveConfig;
        $this->resolverProvider = $resolverProvider;
    }

    public function hasResolver(DirectiveNode $directive): bool
    {
        return \class_exists($this->directiveConfig->getDirectiveResolverFullClassName(
            $this->getDirectiveDefinition($directive))
        );
    }

    public function generate(DirectiveNode $directive): string
    {
        if ($this->hasResolver($directive)) {
            return $this->resolverProvider->generate(
                $this->directiveConfig, $this->getDirectiveDefinition($directive)
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