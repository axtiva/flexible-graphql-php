<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation;

use GraphQL\Type\Definition\Type;
use Axtiva\FlexibleGraphql\Generator\Exception\UnsupportedType;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\ScalarTypeGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\TypeGeneratorInterface;

abstract class AbstractCompositeTypeGenerator
{
    /**
     * @var TypeGeneratorInterface[]|ScalarTypeGeneratorInterface[]
     */
    protected array $generators = [];

    public function isSupportedType(Type $type): bool
    {
        foreach ($this->generators as $generator) {
            if ($generator->isSupportedType($type)) {
                return true;
            }
        }

        return false;
    }

    public function generate(Type $type): string
    {
        foreach ($this->generators as $generator) {
            if ($generator->isSupportedType($type)) {
                return $generator->generate($type);
            }
        }

        throw new UnsupportedType(get_class($type));
    }
}