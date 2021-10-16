<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation;

use Axtiva\FlexibleGraphql\Generator\TypeRegistry\ScalarTypeGeneratorInterface;

class CompositeScalarTypeGenerator extends AbstractCompositeTypeGenerator implements ScalarTypeGeneratorInterface
{
    public function __construct(ScalarTypeGeneratorInterface ...$generators)
    {
        $this->generators = $generators;
    }
}