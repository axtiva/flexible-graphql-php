<?php

namespace Axtiva\FlexibleGraphql\Generator\Model\Foundation\PHPParser;

use Axtiva\FlexibleGraphql\Generator\Model\FieldProviderInterface;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
class PropertyNodeVisitor extends NodeVisitorAbstract implements FieldProviderInterface
{
    private array $variables = [];

    public function beforeTraverse(array $nodes)
    {
        $this->variables = [];
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Property) {
            $prop = $node->props[0];
            $this->variables[(string) $prop->name] = $node;
        }
    }

    /**
     * @return array<string, string> <variable name, variable type>
     */
    public function getResults(): iterable
    {
        return $this->variables;
    }
}