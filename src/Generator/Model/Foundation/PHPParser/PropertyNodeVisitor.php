<?php

namespace Axtiva\FlexibleGraphql\Generator\Model\Foundation\PHPParser;

use Axtiva\FlexibleGraphql\Generator\Model\FieldProviderInterface;
use PhpParser\Node;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
class PropertyNodeVisitor extends NodeVisitorAbstract implements FieldProviderInterface
{
    /**
     * @var array<string, Node\Stmt\Property>
     */
    private array $variables = [];

    /**
     * @param array<Node> $nodes
     */
    public function beforeTraverse(array $nodes): ?array
    {
        $this->variables = [];

        return null;
    }

    public function leaveNode(Node $node): Node|int|null
    {
        if ($node instanceof Node\Stmt\Property) {
            $prop = $node->props[0];
            $this->variables[(string) $prop->name] = $node;
        }

        return null;
    }

    /**
     * @return array<string, Node\Stmt\Property>
     */
    public function getResults(): array
    {
        return $this->variables;
    }
}
