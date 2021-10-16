<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\Exception;

use RuntimeException;
use Throwable;

class UnsupportedType extends RuntimeException
{
    public function __construct($message, $code = 0, Throwable $previous = null)
    {
        parent::__construct('Unsupported type for generation: ' . $message, $code, $previous);
    }
}