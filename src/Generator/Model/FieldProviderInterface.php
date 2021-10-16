<?php

namespace Axtiva\FlexibleGraphql\Generator\Model;

use PhpParser\NodeVisitor;

interface FieldProviderInterface extends NodeVisitor
{
    /**
     * return collected information
     */
    public function getResults(): iterable;
}