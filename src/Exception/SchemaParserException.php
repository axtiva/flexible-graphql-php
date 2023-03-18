<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Exception;

use Exception;
use Throwable;

class SchemaParserException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message ?: "Schema parser exception", $code, $previous);
    }
}