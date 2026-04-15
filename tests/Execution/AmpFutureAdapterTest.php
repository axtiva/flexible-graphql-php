<?php

declare(strict_types=1);

namespace Axtiva\FlexibleGraphql\Tests\Execution;

use Amp\DeferredFuture;
use Amp\Future;
use Axtiva\FlexibleGraphql\Executor\AmpFutureAdapter;
use GraphQL\Error\InvariantViolation;
use GraphQL\Executor\Promise\Promise;
use PHPUnit\Framework\TestCase;
use Revolt\EventLoop;

/**
 * Unit tests for AmpFutureAdapter — the Amphp v3 PromiseAdapter implementation.
 */
class AmpFutureAdapterTest extends TestCase
{
    private AmpFutureAdapter $adapter;

    protected function setUp(): void
    {
        $this->adapter = new AmpFutureAdapter();
    }

    // ------------------------------------------------------------------ isThenable

    public function testIsThenableReturnsTrueForFuture(): void
    {
        $future = Future::complete(42);
        $this->assertTrue($this->adapter->isThenable($future));
    }

    public function testIsThenableReturnsFalseForNonFuture(): void
    {
        $this->assertFalse($this->adapter->isThenable(42));
        $this->assertFalse($this->adapter->isThenable('string'));
        $this->assertFalse($this->adapter->isThenable(null));
        $this->assertFalse($this->adapter->isThenable(new \stdClass()));
    }

    // ------------------------------------------------------------------ convertThenable

    public function testConvertThenableWrapsAFutureInPromise(): void
    {
        $future = Future::complete('hello');
        $promise = $this->adapter->convertThenable($future);

        $this->assertInstanceOf(Promise::class, $promise);
        $this->assertSame($future, $promise->adoptedPromise);
    }

    public function testConvertThenableThrowsForNonFuture(): void
    {
        $this->expectException(InvariantViolation::class);
        $this->adapter->convertThenable('not-a-future');
    }

    // ------------------------------------------------------------------ createFulfilled

    public function testCreateFulfilledWithScalarValue(): void
    {
        $promise = $this->adapter->createFulfilled(99);

        $this->assertInstanceOf(Promise::class, $promise);
        $this->assertInstanceOf(Future::class, $promise->adoptedPromise);

        $value = null;
        EventLoop::queue(static function () use ($promise, &$value): void {
            /** @var Future<mixed> $future */
            $future = $promise->adoptedPromise;
            $value = $future->await();
        });
        EventLoop::run();

        $this->assertSame(99, $value);
    }

    public function testCreateFulfilledWithFuturePassthrough(): void
    {
        $future = Future::complete('amp-value');
        $promise = $this->adapter->createFulfilled($future);

        $this->assertInstanceOf(Promise::class, $promise);
        $this->assertSame($future, $promise->adoptedPromise);
    }

    public function testCreateFulfilledWithPromisePassthrough(): void
    {
        $inner = $this->adapter->createFulfilled('inner');
        $outer = $this->adapter->createFulfilled($inner);

        $this->assertSame($inner, $outer);
    }

    // ------------------------------------------------------------------ createRejected

    public function testCreateRejectedStoresErrorFuture(): void
    {
        $exception = new \RuntimeException('test error');
        $promise = $this->adapter->createRejected($exception);

        $this->assertInstanceOf(Promise::class, $promise);
        $this->assertInstanceOf(Future::class, $promise->adoptedPromise);

        $caught = null;
        EventLoop::queue(static function () use ($promise, &$caught): void {
            try {
                /** @var Future<mixed> $future */
                $future = $promise->adoptedPromise;
                $future->await();
            } catch (\Throwable $e) {
                $caught = $e;
            }
        });
        EventLoop::run();

        $this->assertSame($exception, $caught);
    }

    // ------------------------------------------------------------------ then

    public function testThenCallsOnFulfilledWithResolvedValue(): void
    {
        $future = Future::complete(7);
        $promise = $this->adapter->convertThenable($future);

        $result = null;
        $chainedPromise = $this->adapter->then(
            $promise,
            static function ($value) use (&$result): int {
                $result = $value;
                return $value * 2;
            }
        );

        $this->assertInstanceOf(Promise::class, $chainedPromise);

        EventLoop::queue(static function () use ($chainedPromise): void {
            /** @var Future<mixed> $future */
            $future = $chainedPromise->adoptedPromise;
            $future->await();
        });
        EventLoop::run();

        $this->assertSame(7, $result);
    }

    public function testThenReturnsTransformedValueFromOnFulfilled(): void
    {
        $future = Future::complete(3);
        $promise = $this->adapter->convertThenable($future);

        $chainedPromise = $this->adapter->then(
            $promise,
            static fn($v) => $v + 10
        );

        $resolved = null;
        EventLoop::queue(static function () use ($chainedPromise, &$resolved): void {
            /** @var Future<mixed> $future */
            $future = $chainedPromise->adoptedPromise;
            $resolved = $future->await();
        });
        EventLoop::run();

        $this->assertSame(13, $resolved);
    }

    public function testThenPassesThroughWhenNoOnFulfilled(): void
    {
        $future = Future::complete('raw');
        $promise = $this->adapter->convertThenable($future);

        $chainedPromise = $this->adapter->then($promise);

        $resolved = null;
        EventLoop::queue(static function () use ($chainedPromise, &$resolved): void {
            /** @var Future<mixed> $future */
            $future = $chainedPromise->adoptedPromise;
            $resolved = $future->await();
        });
        EventLoop::run();

        $this->assertSame('raw', $resolved);
    }

    public function testThenCallsOnRejectedOnException(): void
    {
        $exception = new \RuntimeException('bang');
        $future = Future::error($exception);
        $promise = $this->adapter->convertThenable($future);

        $caught = null;
        $chainedPromise = $this->adapter->then(
            $promise,
            null,
            static function (\Throwable $reason) use (&$caught): string {
                $caught = $reason;
                return 'recovered';
            }
        );

        $resolved = null;
        EventLoop::queue(static function () use ($chainedPromise, &$resolved): void {
            /** @var Future<mixed> $future */
            $future = $chainedPromise->adoptedPromise;
            $resolved = $future->await();
        });
        EventLoop::run();

        $this->assertSame($exception, $caught);
        $this->assertSame('recovered', $resolved);
    }

    public function testThenRethrowsWhenNoOnRejected(): void
    {
        $exception = new \RuntimeException('unhandled');
        $future = Future::error($exception);
        $promise = $this->adapter->convertThenable($future);

        $chainedPromise = $this->adapter->then($promise);

        $caught = null;
        EventLoop::queue(static function () use ($chainedPromise, &$caught): void {
            try {
                /** @var Future<mixed> $future */
                $future = $chainedPromise->adoptedPromise;
                $future->await();
            } catch (\Throwable $e) {
                $caught = $e;
            }
        });
        EventLoop::run();

        $this->assertSame($exception, $caught);
    }

    public function testThenThrowsWhenAdoptedPromiseIsNotFuture(): void
    {
        $promise = new Promise('not-a-future', $this->adapter);

        $this->expectException(InvariantViolation::class);
        $this->adapter->then($promise);
    }

    // ------------------------------------------------------------------ create

    public function testCreateResolvesWithValue(): void
    {
        $promise = $this->adapter->create(static function ($resolve, $reject): void {
            $resolve(42);
        });

        $this->assertInstanceOf(Promise::class, $promise);

        $resolved = null;
        EventLoop::queue(static function () use ($promise, &$resolved): void {
            /** @var Future<mixed> $future */
            $future = $promise->adoptedPromise;
            $resolved = $future->await();
        });
        EventLoop::run();

        $this->assertSame(42, $resolved);
    }

    public function testCreateRejectsWithException(): void
    {
        $exception = new \RuntimeException('create-reject');
        $promise = $this->adapter->create(static function ($resolve, $reject) use ($exception): void {
            $reject($exception);
        });

        $caught = null;
        EventLoop::queue(static function () use ($promise, &$caught): void {
            try {
                /** @var Future<mixed> $future */
                $future = $promise->adoptedPromise;
                $future->await();
            } catch (\Throwable $e) {
                $caught = $e;
            }
        });
        EventLoop::run();

        $this->assertSame($exception, $caught);
    }

    public function testCreateCatchesExceptionFromResolver(): void
    {
        $exception = new \RuntimeException('thrown-in-resolver');
        $promise = $this->adapter->create(static function ($resolve, $reject) use ($exception): void {
            throw $exception;
        });

        $caught = null;
        EventLoop::queue(static function () use ($promise, &$caught): void {
            try {
                /** @var Future<mixed> $future */
                $future = $promise->adoptedPromise;
                $future->await();
            } catch (\Throwable $e) {
                $caught = $e;
            }
        });
        EventLoop::run();

        $this->assertSame($exception, $caught);
    }

    public function testCreateResolvesWithFutureValue(): void
    {
        $inner = Future::complete('nested');
        $promise = $this->adapter->create(static function ($resolve) use ($inner): void {
            $resolve($inner);
        });

        $resolved = null;
        EventLoop::queue(static function () use ($promise, &$resolved): void {
            /** @var Future<mixed> $future */
            $future = $promise->adoptedPromise;
            $resolved = $future->await();
        });
        EventLoop::run();

        $this->assertSame('nested', $resolved);
    }

    // ------------------------------------------------------------------ all

    public function testAllResolvesArrayOfFutures(): void
    {
        $p1 = $this->adapter->createFulfilled('a');
        $p2 = $this->adapter->createFulfilled('b');
        $p3 = $this->adapter->createFulfilled('c');

        $allPromise = $this->adapter->all([$p1, $p2, $p3]);
        $this->assertInstanceOf(Promise::class, $allPromise);

        $resolved = null;
        EventLoop::queue(static function () use ($allPromise, &$resolved): void {
            /** @var Future<mixed> $future */
            $future = $allPromise->adoptedPromise;
            $resolved = $future->await();
        });
        EventLoop::run();

        $this->assertSame(['a', 'b', 'c'], $resolved);
    }

    public function testAllResolvesEmptyArray(): void
    {
        $allPromise = $this->adapter->all([]);

        $resolved = null;
        EventLoop::queue(static function () use ($allPromise, &$resolved): void {
            /** @var Future<mixed> $future */
            $future = $allPromise->adoptedPromise;
            $resolved = $future->await();
        });
        EventLoop::run();

        $this->assertSame([], $resolved);
    }

    public function testAllPreservesKeys(): void
    {
        $p1 = $this->adapter->createFulfilled(1);
        $p2 = $this->adapter->createFulfilled(2);

        $allPromise = $this->adapter->all(['x' => $p1, 'y' => $p2]);

        $resolved = null;
        EventLoop::queue(static function () use ($allPromise, &$resolved): void {
            /** @var Future<mixed> $future */
            $future = $allPromise->adoptedPromise;
            $resolved = $future->await();
        });
        EventLoop::run();

        $this->assertSame(['x' => 1, 'y' => 2], $resolved);
    }

    public function testAllWithMixedFuturesAndPromises(): void
    {
        $future = Future::complete('future-val');
        $promise = $this->adapter->createFulfilled('promise-val');

        $allPromise = $this->adapter->all([$future, $promise]);

        $resolved = null;
        EventLoop::queue(static function () use ($allPromise, &$resolved): void {
            /** @var Future<mixed> $future */
            $future = $allPromise->adoptedPromise;
            $resolved = $future->await();
        });
        EventLoop::run();

        $this->assertSame(['future-val', 'promise-val'], $resolved);
    }

    public function testAllAcceptsIterator(): void
    {
        $items = (static function () {
            yield 'a' => Future::complete(10);
            yield 'b' => Future::complete(20);
        })();

        $allPromise = $this->adapter->all($items);

        $resolved = null;
        EventLoop::queue(static function () use ($allPromise, &$resolved): void {
            /** @var Future<mixed> $future */
            $future = $allPromise->adoptedPromise;
            $resolved = $future->await();
        });
        EventLoop::run();

        $this->assertSame(['a' => 10, 'b' => 20], $resolved);
    }

    // ------------------------------------------------------------------ deferred resolution via create+then chain

    public function testDeferredFutureResolvesCorrectly(): void
    {
        $deferred = new DeferredFuture();

        $promise = $this->adapter->convertThenable($deferred->getFuture());

        $result = null;
        $chainedPromise = $this->adapter->then(
            $promise,
            static function ($value) use (&$result): mixed {
                $result = $value;
                return $value;
            }
        );

        EventLoop::queue(static function () use ($deferred, $chainedPromise): void {
            $deferred->complete('deferred-result');
            /** @var Future<mixed> $future */
            $future = $chainedPromise->adoptedPromise;
            $future->await();
        });
        EventLoop::run();

        $this->assertSame('deferred-result', $result);
    }
}
