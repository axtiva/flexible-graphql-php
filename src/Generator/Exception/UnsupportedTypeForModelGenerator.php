<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\Exception;

use Axtiva\FlexibleGraphql\Generator\Model\ModelGeneratorInterface;
use GraphQL\Type\Definition\Type;
use RuntimeException;
use Throwable;

class UnsupportedTypeForModelGenerator extends RuntimeException
{
    public function __construct(Type $type, ModelGeneratorInterface $generator, $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Unsupported type for model generation: %s, %s', $type->toString(), get_class($generator)),
            $code, $previous
        );
    }
}