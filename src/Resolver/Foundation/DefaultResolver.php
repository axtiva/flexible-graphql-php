<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Resolver\Foundation;

use GraphQL\Executor\Executor;
use GraphQL\Type\Definition\ResolveInfo;
use Axtiva\FlexibleGraphql\Resolver\ResolverInterface;

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

    public function __invoke($rootValue, $args, $context, ResolveInfo $info)
    {
        return Executor::defaultFieldResolver($rootValue, $args, $context, $info);
    }
}