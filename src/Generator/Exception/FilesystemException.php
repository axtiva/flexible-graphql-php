<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\Exception;

use RuntimeException;
use Throwable;

class FilesystemException extends RuntimeException
{
    public function __construct($path, $code = 0, Throwable $previous = null)
    {
        parent::__construct('Can not access to path: ' . $path, $code, $previous);
    }
}