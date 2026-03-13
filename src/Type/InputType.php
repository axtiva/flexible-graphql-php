<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Type;

use ArrayObject;

/**
 * @extends ArrayObject<string, mixed>
 */
abstract class InputType extends ArrayObject
{
    /**
     * @param array<string, mixed> $array
     */
    public function __construct(array $array = [])
    {
        parent::__construct($array);
    }

    public function __get(string $name): mixed
    {
        return $this->offsetGet($name);
    }

    public function __set(string $name, mixed $value): void
    {
        $this[$name] = $value;
    }

    public function __unset(string $name): void
    {
        unset($this[$name]);
    }

    public function offsetGet(mixed $key): mixed
    {
        return isset($this[$key]) ? $this->decorate($key, parent::offsetGet($key)) : null;
    }

    abstract protected function decorate(string $name, mixed $value): mixed;
}
