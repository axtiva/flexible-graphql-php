<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\Serializer\Foundation;

use Axtiva\FlexibleGraphql\Generator\Serializer\VariableSerializerInterface;

class VariableSerializer implements VariableSerializerInterface
{
    public function serialize($value): string
    {
        return var_export($value, true);
    }
}