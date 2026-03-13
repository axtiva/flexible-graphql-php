<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Resolver\Foundation;

use GraphQL\Executor\Executor;
use GraphQL\Type\Definition\ResolveInfo;
use Axtiva\FlexibleGraphql\Resolver\ResolverInterface;
use ArrayAccess;

final class DefaultResolver implements ResolverInterface
{

    private static ?DefaultResolver $instance = null;

    public static function getInstance(): ResolverInterface
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function __invoke(mixed $rootValue, array|ArrayAccess|null $args, mixed $context, ResolveInfo $info): mixed
    {
        $normalizedArgs = is_array($args) ? $args : [];
        return Executor::defaultFieldResolver($rootValue, $normalizedArgs, $context, $info);
    }
}
