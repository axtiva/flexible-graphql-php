<?php

namespace Axtiva\FlexibleGraphql\Builder;

use Axtiva\FlexibleGraphql\Generator\Code\CodeGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\Config\CodeGeneratorConfigInterface;

interface CodeGeneratorBuilderInterface
{
    public function getConfig(): CodeGeneratorConfigInterface;
    public function build(): CodeGeneratorInterface;
}