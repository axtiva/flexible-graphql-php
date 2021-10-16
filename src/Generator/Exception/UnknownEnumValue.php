<?php

namespace Axtiva\FlexibleGraphql\Generator\Exception;

use RuntimeException;
use Throwable;

class UnknownEnumValue extends RuntimeException
{
    public function __construct($className, $value, $code = 0, Throwable $previous = null)
    {
        parent::__construct('Unknown enum value : ' . $className . ' ' . $value, $code, $previous);
    }
}