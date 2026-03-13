<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\Serializer\Foundation;

use Axtiva\FlexibleGraphql\Generator\Serializer\VariableSerializerInterface;

class VariableSerializer implements VariableSerializerInterface
{
    public function serialize(mixed $value): string
    {
        return $this->serializeValue($value);
    }

    private function serializeValue(mixed $value): string
    {
        if (
            is_int($value)
            || is_float($value)
            || is_bool($value)
            || $value === null
        ) {
            return var_export($value, true);
        }

        if (is_string($value)) {
            return var_export($value, true);
        }

        if (is_array($value)) {
            $parts = [];
            foreach ($value as $key => $item) {
                $serializedItem = $this->serializeValue($item);
                if (is_int($key)) {
                    $parts[] = $serializedItem;
                    continue;
                }

                $parts[] = $this->serializeValue((string) $key) . ' => ' . $serializedItem;
            }

            return '[' . implode(', ', $parts) . ']';
        }

        return var_export($value, true);
    }
}
