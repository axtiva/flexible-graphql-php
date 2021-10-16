<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation;

use Axtiva\FlexibleGraphql\Generator\TypeRegistry\TypeGeneratorInterface;

class CompositeTypeGenerator extends AbstractCompositeTypeGenerator implements TypeGeneratorInterface
{
    public function __construct(TypeGeneratorInterface ...$generators)
    {
        $this->generators = $generators;
    }
}