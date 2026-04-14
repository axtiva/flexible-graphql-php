<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Tests\Execution;

use Axtiva\FlexibleGraphql\Example\PsrContainerExample;
use Axtiva\FlexibleGraphql\Tests\Execution\Amphp\AmphpTypeRegistry;
use Axtiva\FlexibleGraphql\Tests\Execution\Amphp\SumResolver;
use Amp\Future;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use PHPUnit\Framework\TestCase;
use Revolt\EventLoop;

/**
 * Validates that the AMPHP v3 integration works correctly at runtime.
 *
 * The TypeRegistryGeneratorBuilderAmphp builder generates TypeRegistry code where every
 * field resolver closure is wrapped with \Amp\async(), matching the pattern tested here.
 */
class AmphpV3ExecutionTest extends TestCase
{
    public function testAmphpV3WrappedResolverReturnsFuture(): void
    {
        $container = new PsrContainerExample([
            SumResolver::class => new SumResolver(),
        ]);

        $typeRegistry = new AmphpTypeRegistry($container);
        $queryType = $typeRegistry->Query();

        $this->assertInstanceOf(ObjectType::class, $queryType);

        $fields = $queryType->getFields();
        $this->assertArrayHasKey('sum', $fields);

        $resolveCallback = $fields['sum']->resolveFn;
        $this->assertNotNull($resolveCallback);

        /** @var ResolveInfo $resolveInfo */
        $resolveInfo = $this->createStub(ResolveInfo::class);
        $result = $resolveCallback(null, [], null, $resolveInfo);

        $this->assertInstanceOf(Future::class, $result);
    }

    public function testAmphpV3WrappedResolverResolvesCorrectValue(): void
    {
        $container = new PsrContainerExample([
            SumResolver::class => new SumResolver(),
        ]);

        $typeRegistry = new AmphpTypeRegistry($container);
        $queryType = $typeRegistry->Query();

        $fields = $queryType->getFields();
        $resolveCallback = $fields['sum']->resolveFn;
        $this->assertNotNull($resolveCallback);

        /** @var ResolveInfo $resolveInfo */
        $resolveInfo = $this->createStub(ResolveInfo::class);

        /** @var Future<mixed> $future */
        $future = $resolveCallback(null, [], null, $resolveInfo);

        $resolvedValue = null;

        $fiber = new \Fiber(function () use ($future, &$resolvedValue): void {
            $resolvedValue = $future->await();
        });

        $fiber->start();
        EventLoop::run();

        $this->assertSame(2, $resolvedValue);
    }
}
