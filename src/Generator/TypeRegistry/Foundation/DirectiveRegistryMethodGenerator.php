<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation;

use GraphQL\Type\Definition\Directive;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\DirectiveGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\DirectiveRegistryMethodGeneratorInterface;

class DirectiveRegistryMethodGenerator implements DirectiveRegistryMethodGeneratorInterface
{
    private DirectiveGeneratorInterface $directiveGenerator;

    public function __construct(DirectiveGeneratorInterface $directiveGenerator)
    {
        $this->directiveGenerator = $directiveGenerator;
    }

    public function getMethodCall(Directive $directive): string
    {
        return "\$this->{$this->getMethodName($directive)}()";
    }

    public function getMethod(Directive $directive): string
    {
            return sprintf('
    public function %s()
    {
        static $directive = null;
        if ($directive === null) {
            $directive = %s;
        }
        
        return $directive;
    }
        ', $this->getMethodName($directive), $this->directiveGenerator->generate($directive));
    }

    private function getMethodName(Directive $directive): string
    {
        return "directive_{$directive->name}";
    }
}