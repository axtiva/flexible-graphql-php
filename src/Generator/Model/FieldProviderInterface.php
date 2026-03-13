<?php

namespace Axtiva\FlexibleGraphql\Generator\Model;

use PhpParser\NodeVisitor;

interface FieldProviderInterface extends NodeVisitor
{
    /**
     * return collected information
     *
     * @return array<string, mixed>
     */
    public function getResults(): array;
}
