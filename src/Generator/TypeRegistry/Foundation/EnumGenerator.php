<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation;

use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\Type;
use Axtiva\FlexibleGraphql\Generator\Exception\UnsupportedType;
use Axtiva\FlexibleGraphql\Generator\Serializer\VariableSerializerInterface;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\TypeGeneratorInterface;

class EnumGenerator implements TypeGeneratorInterface
{
    private VariableSerializerInterface $serializer;

    public function __construct(VariableSerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function isSupportedType(Type $type): bool
    {
        return $type instanceof EnumType;
    }

    /**
     * @param Type|EnumType $type
     * @return string
     */
    public function generate(Type $type): string
    {
        if (false === $this->isSupportedType($type)) {
            throw new UnsupportedType(sprintf('Unsupported type %s for %s', $type->name, __CLASS__));
        }

        $values = [];
        foreach ($type->getValues() as $value) {
            $values[] = "{$this->serializer->serialize($value->name)} => [
            'name' => {$this->serializer->serialize($value->name)}, 
            'value' => {$this->serializer->serialize($value->value)},
            'description' => {$this->serializer->serialize($value->description)},
            'deprecationReason' => {$this->serializer->serialize($value->deprecationReason)},
            ]";
        }

        $values = implode("," . PHP_EOL, $values);
        return "new EnumType([
        'name' => {$this->serializer->serialize($type->name)},
        'description' => {$this->serializer->serialize($type->description)},
        'values' => [{$values}],
        ])";
    }
}