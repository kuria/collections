<?php declare(strict_types=1);

namespace Kuria\Collections;

use PHPUnit\Framework\TestCase;

class IterableHelperTest extends TestCase
{
    /**
     * @dataProvider provideIterables
     */
    function testShouldConvertIterableToList(iterable $iterable, array $expectedArray)
    {
        $this->assertSame(array_values($expectedArray), IterableHelper::iterableToList($iterable));
    }

    /**
     * @dataProvider provideIterables
     */
    function testShouldConvertIterableToArray(iterable $iterable, array $expectedArray)
    {
        $this->assertSame($expectedArray, IterableHelper::iterableToArray($iterable));
    }

    function testShouldConvertIterablesToArrays()
    {
        $iterablesAndExpectedArrays = $this->provideIterables();

        $iterables = array_column($iterablesAndExpectedArrays, 0);
        $expectedArrays = array_column($iterablesAndExpectedArrays, 1);

        $this->assertSame($expectedArrays, IterableHelper::iterablesToArrays(...$iterables));
    }

    function provideIterables(): array
    {
        return [
            // iterable, expectedArray
            [
                [],
                [],
            ],

            [
                [1, 2, 3],
                [1, 2, 3],
            ],

            [
                new \ArrayIterator(['foo', 'bar', 'baz']),
                ['foo', 'bar', 'baz'],
            ],

            [
                (function () {
                    yield 'lorem';
                    yield 'ipsum';
                    yield 'dolor';
                })(),
                ['lorem', 'ipsum', 'dolor'],
            ],

            [
                ['one' => 1, 'two' => 2],
                ['one' => 1, 'two' => 2],
            ],

            [
                new \ArrayIterator(['a' => 'foo', 'b' => 'bar']),
                ['a' => 'foo', 'b' => 'bar'],
            ],

            [
                (function () {
                    yield 'x'=> 'lorem';
                    yield 'y' => 'ipsum';
                })(),
                ['x' => 'lorem', 'y' => 'ipsum'],
            ],
        ];
    }
}
