<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql;

use Axtiva\FlexibleGraphql\Exception\EmptyRepresentation;
use Axtiva\FlexibleGraphql\Exception\RepresentationDoesNotHaveTypeNameField;

class Representation
{

    /**
     * @var string name of type like __typename graphql field
     */
    private string $typeName;

    /**
     * @var array<string, mixed> Set of ids for each type from @key or @requires directives
     */
    private array $fields;

    /**
     * @param array<string, mixed> $representation
     */
    public function __construct(array $representation)
    {
        if (empty($representation['__typename']) || !is_string($representation['__typename'])) {
            throw new RepresentationDoesNotHaveTypeNameField();
        }

        $this->typeName = $representation['__typename'];

        unset($representation['__typename']);

        if (empty($representation)) {
            throw new EmptyRepresentation();
        }

        $this->fields = $representation;
    }

    public function getTypename(): string
    {
        return $this->typeName;
    }

    public function getFields(): array
    {
        return $this->fields;
    }
}
