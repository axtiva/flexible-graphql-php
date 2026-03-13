<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Resolver\Foundation;

use Axtiva\FlexibleGraphql\Resolver\_ServiceResolverInterface;
use GraphQL\Type\Definition\ResolveInfo;
use ArrayAccess;

class _ServiceResolver implements _ServiceResolverInterface
{
    private string $schema;

    public function __construct(string $graphqlSchemaSDL)
    {
        $this->schema = $graphqlSchemaSDL;
    }

    public function __invoke(mixed $rootValue, array|ArrayAccess|null $args, mixed $context, ResolveInfo $info): mixed
    {
        return [
            'sdl' => $this->schema,
        ];
    }
}
