<?php declare(strict_types=1);

namespace Kuria\Collections;

use PHPUnit\Framework\TestCase;

class MapTest extends TestCase
{
    /**
     * @dataProvider provideConstructorPairs
     */
    function testShouldCreateMap(?iterable $pairs, array $expectedArrayData)
    {
        $this->assertSame($expectedArrayData, (new Map($pairs))->toArray());
    }

    function provideConstructorPairs(): array
    {
        $pairs = ['foo' => 'bar', 'baz' => new \stdClass()];

        return [
            // pairs, expectedArrayData
            'should accept null' => [null, []],
            'should accept empty array' => [[], []],
            'should accept empty traversable' => [new \ArrayObject(), []],
            'should accept non-empty array' => [$pairs, $pairs],
            'should accept non-empty traversable' => [new Map($pairs), $pairs],
        ];
    }

    /**
     * @dataProvider provideKeysAndValuesToCombine
     */
    function testShouldCombine($keys, $values, array $expectedPairs)
    {
        $this->assertMap($expectedPairs, Map::combine($keys, $values));
    }

    function provideKeysAndValuesToCombine(): array
    {
        return [
            // keys, values, expectedPairs
            [
                [],
                [],
                [],
            ],
            [
                ['foo', 'bar'],
                ['baz', 'qux'],
                ['foo' => 'baz', 'bar' => 'qux'],
            ],
            [
                [123, 456],
                [789, 101],
                [123 => 789, 456 => 101],
            ],
            [
                new Collection(['one','two']),
                new Collection([1, 2]),
                ['one' => 1, 'two' => 2],
            ],
        ];
    }

    function testShouldConvertToArray()
    {
        $this->assertSame($this->getExamplePairs(), $this->getExampleMap()->toArray());
    }

    function testShouldCheckIfMapIsEmpty()
    {
        $this->assertTrue((new Map())->isEmpty());
        $this->assertFalse($this->getExampleMap()->isEmpty());
    }

    function testShouldCheckIfKeyExists()
    {
        $map = $this->getExampleMap();

        $this->assertTrue($map->has('foo'));
        $this->assertTrue($map->has('baz'));
        $this->assertTrue($map->has(123));
        $this->assertTrue($map->has('123'));
        $this->assertFalse($map->has(' foo'));
        $this->assertFalse($map->has('baz '));
        $this->assertFalse($map->has(' 123'));
        $this->assertFalse($map->has('123 '));
    }

    /**
     * @dataProvider provideKeysToAccess
     */
    function testShouldCheckKeyAndGetValue(array $pairs, $key, bool $exists, $expectedValue = null)
    {
        $map = new Map($pairs);

        $this->assertSame($exists, $map->has($key));
        $this->assertSame($expectedValue, $map->get($key));
    }

    function provideKeysToAccess()
    {
        $pairs = $this->getExamplePairs();
        $pairs['null'] = null;

        return [
            // pairs, key, exists, expectedValue
            [$pairs, '', false, null],
            [$pairs, 'foo', true, 'bar'],
            [$pairs, 'baz', true, 'qux'],
            [$pairs, 123, true, 456],
            [$pairs, 'null', true, null],
            [$pairs, 'quux', false, null],
            [$pairs, 0, false, null],
            [$pairs, 111, false, null],
        ];
    }

    /**
     * @dataProvider provideKeysAndValues
     */
    function testShouldCheckAndFindValue(array $pairs, $value, bool $strict, $expectedKey)
    {
        $map = new Map($pairs);

        $this->assertSame($expectedKey !== null, $map->contains($value, $strict));
        $this->assertSame($expectedKey, $map->find($value, $strict));
    }

    function provideKeysAndValues(): array
    {
        $object = (object) ['property' => 'value'];

        $pairs = $this->getExamplePairs();

        $objectValues = [
            'lorem' => new \stdClass(),
            'ipsum' => $object,
        ];

        $arrayValues = [
            ['key' => 111],
            ['key' => 123],
        ];

        return [
            // pairs, value, strict, expectedKey
            [$pairs, 'bar', true, 'foo'],
            [$pairs, 'qux', true, 'baz'],
            [$pairs, 456, true, 123],
            [$pairs, '456', true, null],
            [$pairs, '456', false, 123],
            [$pairs, 'quux', true, null],
            [$pairs, 'quux', false, null],
            [$objectValues, $object, true, 'ipsum'],
            [$objectValues, clone $object, true, null],
            [$objectValues, clone $object, false, 'ipsum'],
            [$arrayValues, ['key' => 123], true, 1],
            [$arrayValues, ['key' => '123'], true, null],
            [$arrayValues, ['key' => '123'], false, 1],
        ];
    }

    /**
     * @dataProvider providePairs
     */
    function testShouldGetValuesAndKeys(array $pairs)
    {
        $map = new Map($pairs);

        $this->assertSame(array_values($pairs), $map->values()->toArray());
        $this->assertSame(array_keys($pairs), $map->keys()->toArray());
    }

    function providePairs(): array
    {
        return [
            // pairs
            [$this->getExamplePairs()],
            [[]],
        ];
    }

    function testShouldSet()
    {
        $map = new Map();

        $map->set('foo', 'bar');
        $map->set('null', null);
        $map->set('123', '456');

        $this->assertMap(['foo' => 'bar', 'null' => null, 123 => '456'], $map);
    }

    function testShouldAdd()
    {
        $map = $this->getExampleMap();

        $map->add(['foo' => 'new bar', 'quux' => 'corge']);
        $map->add(new Map([123 => 445566]), [789 => 101]);

        $this->assertMap(
            [
                'foo' => 'new bar',
                'baz' => 'qux',
                123 => 445566,
                'quux' => 'corge',
                789 => 101,
            ],
            $map
        );
    }

    function testShouldFill()
    {
        $map = new Map();

        $map->fill(['foo', 'bar'], '');
        $map->fill(new Collection(['bar', 'baz']), 123);

        $this->assertMap(
            [
                'foo' => '',
                'bar' => 123,
                'baz' => 123,
            ],
            $map
        );
    }

    function testShouldRemove()
    {
        $map = $this->getExampleMap();

        $map->remove('foo');

        $this->assertMap(
            [
                'baz' => 'qux',
                123 => 456,
            ],
            $map
        );

        $map->remove('bar'); // nonexistent
        $map->remove(999); // nonexistent

        $this->assertMap(
            [
                'baz' => 'qux',
                123 => 456,
            ],
            $map
        );

        $map->remove('baz', 123, 'nonexistent');

        $this->assertMap([], $map);
    }

    function testShouldClear()
    {
        $map = $this->getExampleMap();
        $map->clear();

        $this->assertMap([], $map);
    }

    /**
     * @dataProvider providePairsToReduce
     */
    function testShouldReduce(array $pairs, callable $reducer, $initial, $expectedResult)
    {
        $this->assertSame($expectedResult, (new Map($pairs))->reduce($reducer, $initial));
    }

    function providePairsToReduce(): array
    {
        $reducer = function ($result, $key, $value) {
            if ($result !== null) {
                $result .= ';';
            }

            $result .= "{$key}:{$value}";

            return $result;
        };

        return [
            // pairs, reducer, initial, expectedResult
            [[], $reducer, null, null],
            [[], $reducer, 'foo', 'foo'],
            [['foo' => 'bar', 'baz' => 'qux'], $reducer, null, 'foo:bar;baz:qux'],
            [['a' => 'b', 'c' => 'd'], $reducer, 'test', 'test;a:b;c:d'],
        ];
    }

    function testShouldFlip()
    {
        $map = $this->getExampleMap();

        $flipped = $map->flip();

        $this->assertMap(
            [
                'bar' => 'foo',
                'qux' => 'baz',
                456 => 123,
            ],
            $flipped
        );

        $this->assertNotSame($map, $flipped); // should return new instance
    }

    function testShouldShuffle()
    {
        $map = $this->getExampleMap();

        $shuffled = $map->shuffle();

        $this->assertTrue($shuffled->has('foo'));
        $this->assertTrue($shuffled->has('baz'));
        $this->assertTrue($shuffled->has(123));
        $this->assertSame(3, $shuffled->count());
        $this->assertNotSame($map, $shuffled); // should return new instance
    }

    /**
     * @dataProvider providePairsToColumn
     */
    function testShouldGetColumn(array $pairs, $key, $indexKey, array $expectedColumn)
    {
        $map = new Map($pairs);
        $column = $map->column($key, $indexKey);

        $this->assertMap($expectedColumn, $column);
        $this->assertNotSame($map, $column); // should return new instance
    }

    function providePairsToColumn(): array
    {
        $pairs = [
            'array' => ['foo' => 'bar', 'baz' => 'qux'],
            'object' => new class () {
                public $foo = '123';

                public $baz = '456';
            },
            'protectedObject' => new class () {
                protected $foo = 'private';

                protected $baz = 'private';
            },
            'magicObject' => new class () {
                function __isset($p)
                {
                    return $p === 'foo' || $p === 'baz';
                }

                function __get($p)
                {
                    return 'magic';
                }
            },
            'dynamicObject' => (object) ['foo' => 'a', 'baz' => 'b'],
            'blankObject' => new \stdClass(),
            'string' => 'scalar',
            'int' => 123456,
        ];

        return [
            // pairs, key, indexKey, expectedColumn
            [
                $pairs,
                'foo',
                null,
                ['array' => 'bar', 'object' => '123', 'magicObject' => 'magic', 'dynamicObject' => 'a'],
            ],
            [
                $pairs,
                'foo',
                'baz',
                ['qux' => 'bar', 456 => '123', 'magic' => 'magic', 'b' => 'a'],
            ],
            [
                $pairs,
                'nonexistent',
                null,
                [],
            ],
        ];
    }

    function testShouldFilter()
    {
        $map = $this->getExampleMap();

        $filtered = $map->filter(function ($key, $value) {
            $this->assertContains($value, ['bar', 'qux', 456]);

            return in_array($key, ['foo', 123], true);
        });

        $this->assertMap(['foo' => 'bar', 123 => 456], $filtered);
        $this->assertNotSame($map, $filtered); // should return new instance
    }

    function testShouldMap()
    {
        $map = $this->getExampleMap();

        $mapped = $map->map(function ($key, $value) {
            return [$key . '-2' => $value . '-2'];
        });

        $this->assertMap(['foo-2' => 'bar-2', 'baz-2' => 'qux-2', '123-2' => '456-2'], $mapped);
        $this->assertNotSame($map, $mapped); // should return new instance
    }

    /**
     * @dataProvider providePairsToIntersect
     */
    function testShouldIntersect(array $pairs, array $iterables, array $expectedIntersection)
    {
        $map = new Map($pairs);

        $intersection = $map->intersect(...$iterables);

        $this->assertMap($expectedIntersection, $intersection);
        $this->assertNotSame($map, $intersection); // should return new instance
    }

    function providePairsToIntersect(): array
    {
        return [
            // pairs, iterables, expectedIntersection
            [
                ['foo' => 'bar'],
                [],
                [],
            ],
            [
                [],
                [[]],
                [],
            ],
            [
                ['a' => 'foo', 'b' => 'bar', 'c' => 'baz'],
                [['a' => 'foo', 'b' => 'baz', 'c' => 'baz', 'd' => 'foo']],
                ['a' => 'foo', 'c' => 'baz'],
            ],
            [
                ['a' => 'foo', 'b' => 'bar', 'c' => 'baz'],
                [['a' => 'lorem', 'b' => 'bar', 'c' => 'ipsum'], new Map(['a' => 'dolor', 'b' => 'bar', 'c' => 'bar'])],
                ['b' => 'bar'],
            ],
        ];
    }

    function testShouldIntersectUsingCustomComparator()
    {
        $map = new Map([
            'a' => ['id' => 1, 'value' => 'one'],
            'b' => ['id' => 2, 'value' => 'two'],
            'c' => ['id' => 3, 'value' => 'three'],
            'x' => ['id' => 1, 'value' => 'one'],
        ]);

        $comparator = function (array $a, array $b) {
            return $a['id'] <=> $b['id'];
        };

        $this->assertMap(
            [
                'b' => ['id' => 2, 'value' => 'two'],
            ],
            $map->uintersect(
                $comparator,
                [
                    'b' => ['id' => 2, 'value' => 'bar'],
                    'c' => ['id' => 4, 'value' => 'qux'],
                ],
                [
                    'x' => ['id' => 0, 'value' => 'zero'],
                    'b' => ['id' => 2, 'value' => 'also two'],
                    'e'=> ['id' => 5, 'value' => 'quux'],
                ]
            )
        );
    }

    /**
     * @dataProvider providePairsToDiff
     */
    function testShouldDiff(array $pairs, array $iterables, array $expectedDiff)
    {
        $map = new Map($pairs);

        $diff = $map->diff(...$iterables);

        $this->assertMap($expectedDiff, $diff);
        $this->assertNotSame($map, $diff); // should return new instance
    }

    function providePairsToDiff(): array
    {
        return [
            // pairs, iterables, expectedDiff
            [
                ['foo' => 'bar'],
                [],
                [],
            ],
            [
                [],
                [[]],
                [],
            ],
            [
                ['a' => 'foo', 'b' => 'bar', 'c' => 'baz'],
                [['a' => 'foo', 'c' => 'baz', 'd' => 'qux']],
                ['b' => 'bar'],
            ],
            [
                ['a' => 'foo', 'b' => 'bar', 'c' => 'baz', 'd' => 'qux'],
                [['a' => 'bar', 'b' => 'bar', 'c' => 'qux'], new Map(['c' => 'baz', 'd' => 'quux'])],
                ['a' => 'foo', 'd' => 'qux'],
            ],
        ];
    }

    function testShouldDiffUsingCustomComparator()
    {
        $map = new Map([
            'a' => ['id' => 1, 'value' => 'one'],
            'b' => ['id' => 2, 'value' => 'two'],
            'c' => ['id' => 3, 'value' => 'three'],
            'x' => ['id' => 1, 'value' => 'one'],
        ]);

        $comparator = function (array $a, array $b) {
            return $a['id'] <=> $b['id'];
        };

        $this->assertMap(
            [
                'c' => ['id' => 3, 'value' => 'three'],
            ],
            $map->udiff(
                $comparator,
                [
                    'b' => ['id' => 2, 'value' => 'bar'],
                    'd' => ['id' => 4, 'value' => 'qux'],
                ],
                [
                    'x' => ['id' => 1, 'value' => 'zero'],
                    'a' => ['id' => 1, 'value' => 'also one'],
                    'e' => ['id' => 5, 'value' => 'quux'],
                ]
            )
        );
    }

    /**
     * @dataProvider providePairsToSort
     */
    function testShouldSort(array $unsortedPairs, array $expectedSortedPairs, int $flags = SORT_REGULAR)
    {
        $map = new Map($unsortedPairs);

        $sorted = $map->sort($flags);

        $this->assertMap($expectedSortedPairs, $sorted);
        $this->assertNotSame($map, $sorted); // should return new instance

        $reverseSorted = $map->sort($flags, true);

        $this->assertMap(array_reverse($expectedSortedPairs, true), $reverseSorted);
        $this->assertNotSame($map, $reverseSorted); // should return new instance
    }

    function providePairsToSort(): array
    {
        return [
            // unsortedPairs, expectedSortedPairs, [flags]
            [
                [1, 6, 9, 5, 2, 4, 10, 7, 3, 8],
                [0 => 1, 4 => 2, 8 => 3, 5 => 4, 3 => 5, 1 => 6, 7 => 7, 9 => 8, 2 => 9, 6 => 10],
            ],
            [
                ['1', '6', '9', '5', '2', '4', '10', '7', '3', '8'],
                [0 => '1', 4 => '2', 8 => '3', 5 => '4', 3 => '5', 1 => '6', 7 => '7', 9 => '8', 2 => '9', 6 => '10'],
            ],
            [
                ['foo', 'bar', 'baz'],
                [1 => 'bar', 2 => 'baz', 0 => 'foo'],
            ],
            [
                ['foo.5', 'foo.10', 'foo.1'],
                [2 => 'foo.1', 1 => 'foo.10', 0 => 'foo.5'],
            ],
            [
                ['foo.5', 'foo.10', 'foo.1'],
                [2 => 'foo.1',  0 => 'foo.5', 1 => 'foo.10'],
                SORT_NATURAL,
            ],
            [
                ['bar.5', 'BAR.2', 'bar.1'],
                [1 => 'BAR.2', 2 => 'bar.1', 0 => 'bar.5'],
                SORT_NATURAL,
            ],
            [
                ['bar.5', 'BAR.2', 'bar.1'],
                [2 => 'bar.1', 1 => 'BAR.2', 0 => 'bar.5'],
                SORT_NATURAL | SORT_FLAG_CASE,
            ],
        ];
    }

    function testShouldSortUsingCustomComparator()
    {
        $map = new Map([1, 2, 3, 4]);

        $comparator = function ($a, $b) {
            return ($a <=> $b) * -1;
        };

        $this->assertMap([3 => 4, 2 => 3, 1 => 2, 0 => 1], $map->usort($comparator));
    }

    /**
     * @dataProvider providePairsToKsort
     */
    function testShouldSortUsingKeys(array $unsortedPairs, array $expectedSortedPairs, int $flags = SORT_REGULAR)
    {
        $map = new Map($unsortedPairs);

        $sorted = $map->ksort($flags);

        $this->assertMap($expectedSortedPairs, $sorted);
        $this->assertNotSame($map, $sorted); // should return new instance

        $reverseSorted = $map->ksort($flags, true);

        $this->assertMap(array_reverse($expectedSortedPairs, true), $reverseSorted);
        $this->assertNotSame($map, $reverseSorted); // should return new instance
    }

    function providePairsToKsort(): iterable
    {
        foreach ($this->providePairsToSort() as $dataSet) {
            // unsortedPairs, expectedSortedPairs, flags
            yield [array_flip($dataSet[0]), array_flip($dataSet[1])] + $dataSet;
        }
    }

    function testShouldSortUsingKeysAndCustomComparator()
    {
        $map = new Map(['a' => 1, 'b' => 2, 'c' => 3]);

        $comparator = function ($a, $b) {
            return ($a <=> $b) * -1;
        };

        $this->assertMap(['c' => 3, 'b' => 2, 'a' => 1], $map->uksort($comparator));
    }

    function testShouldCount()
    {
        $this->assertSame(0, (new Map())->count());
        $this->assertSame(3, $this->getExampleMap()->count());
    }

    /**
     * @dataProvider provideKeysToAccess
     */
    function testShouldReadAsArray(array $pairs, $key, bool $exists, $expectedValue = null)
    {
        $map = new Map($pairs);

        $this->assertSame($exists, isset($map[$key]));
        $this->assertSame($expectedValue, $map[$key]);
    }

    function testShouldWriteAsArray()
    {
        $map = $this->getExampleMap();

        // offsetSet()
        $map['foo'] = 'new bar';
        $map['baz'] = 'new qux';
        $map[123] = 789;

        $this->assertMap(['foo' => 'new bar', 'baz' => 'new qux', 123 => 789], $map);

        // offsetUnset()
        unset($map['baz'], $map['foo']);

        $this->assertMap([123 => 789], $map);
    }

    function testShouldGetIterator()
    {
        $this->assertSame(
            $this->getExamplePairs(),
            iterator_to_array($this->getExampleMap()->getIterator())
        );
    }

    function testBlankVarargsShouldShortCircuit()
    {
        $map = $this->getExampleMap();
        $pairs = $map->toArray();

        $callback = function () {
            $this->fail('Callback should not be called when there are no varargs');
        };

        // no-op
        $map->add();
        $this->assertMap($pairs, $map);

        $map->remove();
        $this->assertMap($pairs, $map);

        // empty result
        $this->assertMap([], $map->intersect());
        $this->assertMap([], $map->uintersect($callback));
        $this->assertMap([], $map->diff());
        $this->assertMap([], $map->udiff($callback));
    }

    function testEmptyMapShouldShortCircuit()
    {
        $map = new Map();

        $callback = function () {
            $this->fail('Callback should not be called when the map is empty');
        };

        $this->assertMap([], $map->flip());
        $this->assertMap([], $map->shuffle());
        $this->assertMap([], $map->column('key'));
        $this->assertMap([], $map->column('a', 'b'));
        $this->assertMap([], $map->filter($callback));
        $this->assertMap([], $map->map($callback));
        $this->assertMap([], $map->intersect(['value']));
        $this->assertMap([], $map->uintersect($callback, ['value']));
        $this->assertMap([], $map->diff(['value']));
        $this->assertMap([], $map->udiff($callback, ['value']));
        $this->assertMap([], $map->sort());
        $this->assertMap([], $map->usort($callback));
        $this->assertMap([], $map->ksort());
        $this->assertMap([], $map->uksort($callback));
    }

    private function getExamplePairs(): array
    {
        return [
            'foo' => 'bar',
            'baz' => 'qux',
            123 => 456,
        ];
    }

    private function getExampleMap(): Map
    {
        return new Map($this->getExamplePairs());
    }

    private function assertMap(array $expectedPairs, Map $map): void
    {
        $this->assertSame($expectedPairs, $map->toArray());
    }
}
