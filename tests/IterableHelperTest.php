<?php declare(strict_types=1);

namespace Kuria\Collections;

use Kuria\DevMeta\Test;

class IterableHelperTest extends Test
{
    function testShouldConvertIterablesToArrays()
    {
        $this->assertSame(
            [
                [],
                [1, 2, 3],
                ['foo', 'bar', 'baz'],
                ['lorem', 'ipsum', 'dolor'],
                ['one' => 1, 'two' => 2],
                ['a' => 'foo', 'b' => 'bar'],
                ['x' => 'lorem', 'y' => 'ipsum'],
            ],
            IterableHelper::toArrays(
                [],
                [1, 2, 3],
                new \ArrayIterator(['foo', 'bar', 'baz']),
                (function () {
                    yield 'lorem';
                    yield 'ipsum';
                    yield 'dolor';
                })(),
                ['one' => 1, 'two' => 2],
                new \ArrayIterator(['a' => 'foo', 'b' => 'bar']),
                (function () {
                    yield 'x'=> 'lorem';
                    yield 'y' => 'ipsum';
                })()
            )
        );
    }
}
