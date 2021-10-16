<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Generator\TypeRegistry\Foundation;

use GraphQL\Type\Definition\Directive;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\DirectiveGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\TypeRegistry\FieldArgumentGeneratorInterface;
use Axtiva\FlexibleGraphql\Generator\Serializer\VariableSerializerInterface;

class DirectiveGenerator implements DirectiveGeneratorInterface
{
    private VariableSerializerInterface $serializer;
    private FieldArgumentGeneratorInterface $fieldArgumentGenerator;

    public function __construct(
        VariableSerializerInterface $serializer,
        FieldArgumentGeneratorInterface $fieldArgumentGenerator
    ) {
        $this->serializer = $serializer;
        $this->fieldArgumentGenerator = $fieldArgumentGenerator;
    }

    public function generate(Directive $directive): string
    {
        $locations = "'" . implode("','", $directive->locations) . "'";

        $args = [];
        foreach ($directive->args as $arg) {
            $args[] = $this->fieldArgumentGenerator->generate($arg);
        }

        $args = implode(',', $args);
        return "new Directive([
            'name' => {$this->serializer->serialize($directive->name)},
            'description' => {$this->serializer->serialize($directive->description)},
            'isRepeatable' => {$this->serializer->serialize($directive->isRepeatable)},
            'locations' => [{$locations}],
            'args' => [
                {$args}
            ],
        ])";
    }
}