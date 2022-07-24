<?php

namespace Axtiva\FlexibleGraphql\Type;

use ArrayObject;

abstract class InputType extends ArrayObject
{
    public function __construct($array = [])
    {
        parent::__construct($array);
    }

    public function __get($name)
    {
        return $this->offsetGet($name);
    }

    public function __set($name, $value)
    {
        $this[$name] = $value;
    }

    public function __unset(string $name)
    {
        unset($this[$name]);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($key)
    {
        return isset($this[$key]) ? $this->decorate($key, parent::offsetGet($key)) : null;
    }

    abstract protected function decorate($name, $value);
}