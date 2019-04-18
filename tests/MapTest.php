<?php declare(strict_types=1);

namespace Kuria\Collections;

use Kuria\DevMeta\Test;

class MapTest extends Test
{
    /**
     * @dataProvider providePairsForCreate
     */
    function testShouldCreateMap(?iterable $pairs, array $expectedArrayData)
    {
        $this->assertMap($expectedArrayData, Map::create($pairs));

        if ($pairs !== null) {
            $map = Map::create();
            $map->setPairs($pairs);

            $this->assertMap($expectedArrayData, $map);
        }
    }

    function providePairsForCreate()
    {
        $pairs = ['foo' => 'bar', 'baz' => new \stdClass()];

        return [
            // pairs, expectedArrayData
            'should accept null' => [null, []],
            'should accept empty array' => [[], []],
            'should accept empty traversable' => [new \ArrayObject(), []],
            'should accept non-empty array' => [$pairs, $pairs],
            'should accept non-empty traversable' => [Map::create($pairs), $pairs],
        ];
    }

    /**
     * @dataProvider provideIterablesToBuild
     */
    function testShouldBuild(iterable $iterable, callable $mapper, array $expectedPairs)
    {
        $this->assertMap($expectedPairs, Map::build($iterable, $mapper));
    }

    function provideIterablesToBuild()
    {
        return [
            // iterable, mapper, expectedPairs
            [
                [],
                static function () {
                    static::fail('Mapper should not be called for empty iterables');
                },
                [],
            ],
            [
                [
                    123 => 'foo',
                    456 => 'bar',
                    789 => 'baz',
                ],
                static function ($key, $value) {
                    return ['key.' . $key => 'value.' . $value];
                },
                [
                    'key.123' => 'value.foo',
                    'key.456' => 'value.bar',
                    'key.789' => 'value.baz',
                ],
            ],
            [
                [
                    123 => 'foo',
                    456 => 'bar',
                    789 => 'baz',
                ],
                static function ($key, $value) {
                    return [
                        [123 => 'a', 456 => 'duplicate', 789 => 'duplicate'][$key] => $value,
                    ];
                },
                [
                    'a' => 'foo',
                    'duplicate' => 'bar',
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideKeysAndValuesToCombine
     */
    function testShouldCombine($keys, $values, array $expectedPairs)
    {
        $this->assertMap($expectedPairs, Map::combine($keys, $values));
    }

    function provideKeysAndValuesToCombine()
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
                Collection::collect('one', 'two'),
                Collection::collect(1, 2),
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
        $this->assertTrue((Map::create())->isEmpty());
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
        $map = Map::create($pairs);

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
        $map = Map::create($pairs);

        $this->assertSame($expectedKey !== null, $map->contains($value, $strict));
        $this->assertSame($expectedKey, $map->find($value, $strict));
    }

    function provideKeysAndValues()
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
        $map = Map::create($pairs);

        $this->assertSame(array_values($pairs), $map->values()->toArray());
        $this->assertSame(array_keys($pairs), $map->keys()->toArray());
    }

    function providePairs()
    {
        return [
            // pairs
            [$this->getExamplePairs()],
            [[]],
        ];
    }

    function testShouldSet()
    {
        $map = Map::create();

        $map->set('foo', 'bar');
        $map->set('null', null);
        $map->set('123', '456');

        $this->assertMap(['foo' => 'bar', 'null' => null, 123 => '456'], $map);
    }

    function testShouldAdd()
    {
        $map = $this->getExampleMap();

        $map->add(['foo' => 'new bar', 'quux' => 'corge']);
        $map->add(Map::create([123 => 445566]), [789 => 101]);

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
        $map = Map::create();

        $map->fill(['foo', 'bar'], '');
        $map->fill(Collection::collect('bar', 'baz'), 123);

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
        $this->assertSame($expectedResult, (Map::create($pairs))->reduce($reducer, $initial));
    }

    function providePairsToReduce()
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
            $flipped,
            $map
        );
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
        $map = Map::create($pairs);
        $column = $map->column($key, $indexKey);

        $this->assertMap($expectedColumn, $column, $map);
    }

    function providePairsToColumn()
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

        $this->assertMap(['foo' => 'bar', 123 => 456], $filtered, $map);
    }

    function testShouldApply()
    {
        $map = $this->getExampleMap();

        $applied = $map->apply(function ($key, $value) {
            return $key . $value;
        });

        $this->assertMap(['foo' => 'foobar', 'baz' => 'bazqux', 123 => '123456'], $applied, $map);
    }

    function testShouldMap()
    {
        $map = $this->getExampleMap();

        $mapped = $map->map(function ($key, $value) {
            return [$key . '-2' => $value . '-2'];
        });

        $this->assertMap(['foo-2' => 'bar-2', 'baz-2' => 'qux-2', '123-2' => '456-2'], $mapped, $map);
    }

    /**
     * @dataProvider providePairsToMerge
     */
    function testShouldMerge(array $pairs, array $iterables, array $expectedResult)
    {
        $map = Map::create($pairs);

        $result = $map->merge(...$iterables);

        $this->assertMap($expectedResult, $result, $map);
    }

    function providePairsToMerge()
    {
        return [
            // pairs, iterables, expectedResult
            [
                [],
                [],
                [],
            ],
            [
                [],
                [['foo' => 123, 'bar' => 456]],
                ['foo' => 123, 'bar' => 456],
            ],
            [
                [
                    'foo' => 123,
                    'bar' => 456,
                    'baz' => 789,
                ],
                [
                    ['foo' => 222, 'qux' => 888],
                ],
                [
                    'foo' => 222,
                    'bar' => 456,
                    'baz' => 789,
                    'qux' => 888,
                ],
            ],
            [
                [
                    'foo' => 123,
                    'bar' => 456,
                    'baz' => 789,
                ],
                [
                    ['foo' => 222, 'qux' => 888],
                    Map::create(['bar' => 555, 'qux' => 999]),
                ],
                [
                    'foo' => 222,
                    'bar' => 555,
                    'baz' => 789,
                    'qux' => 999,
                ],
            ],
        ];
    }

    /**
     * @dataProvider providePairsToIntersect
     */
    function testShouldIntersect(array $pairs, array $iterables, array $expectedIntersection)
    {
        $map = Map::create($pairs);

        $intersection = $map->intersect(...$iterables);

        $this->assertMap($expectedIntersection, $intersection, $map);
    }

    function providePairsToIntersect()
    {
        return [
            // pairs, iterables, expectedIntersection
            [
                ['a' => 'foo', 'b' => 'bar', 'c' => 'baz'],
                [['a' => 'foo', 'b' => 'baz', 'c' => 'baz', 'd' => 'foo']],
                ['a' => 'foo', 'c' => 'baz'],
            ],
            [
                ['a' => 'foo', 'b' => 'bar', 'c' => 'baz'],
                [['a' => 'lorem', 'b' => 'bar', 'c' => 'ipsum'], Map::create(['a' => 'dolor', 'b' => 'bar', 'c' => 'bar'])],
                ['b' => 'bar'],
            ],
        ];
    }

    function testShouldIntersectUsingCustomComparator()
    {
        $map = Map::create([
            'a' => ['id' => 1, 'value' => 'one'],
            'b' => ['id' => 2, 'value' => 'two'],
            'c' => ['id' => 3, 'value' => 'three'],
            'x' => ['id' => 1, 'value' => 'one'],
        ]);

        $comparator = function (array $a, array $b) {
            return $a['id'] <=> $b['id'];
        };

        $intersection = $map->uintersect(
            $comparator,
            [
                'b' => ['id' => 2, 'value' => 'bar'],
                'c' => ['id' => 4, 'value' => 'qux'],
            ],
            Map::create([
                'x' => ['id' => 0, 'value' => 'zero'],
                'b' => ['id' => 2, 'value' => 'also two'],
                'e'=> ['id' => 5, 'value' => 'quux'],
            ])
        );

        $this->assertMap(
            [
                'b' => ['id' => 2, 'value' => 'two'],
            ],
            $intersection,
            $map
        );
    }

    /**
     * @dataProvider providePairsForKeyIntersection
     */
    function testShouldIntersectKeys(array $pairs, array $iterables, array $expectedIntersection)
    {
        $map = Map::create($pairs);

        $intersection = $map->intersectKeys(...$iterables);

        $this->assertMap($expectedIntersection, $intersection, $map);
    }

    function providePairsForKeyIntersection()
    {
        return [
            // pairs, iterables, expectedIntersection
            [
                [
                    'foo' => 'bar',
                    'baz' => 'qux',
                    'quux' => 'quuz',
                ],
                [
                    [
                        'lorem' => 'ipsum',
                        'baz' => 'dolor',
                        'quux' => 'sit',
                        'amet' => 123,
                    ],
                ],
                [
                    'baz' => 'qux',
                    'quux' => 'quuz',
                ],
            ],
            [
                [
                    'foo' => 'bar',
                    'baz' => 'qux',
                    'quux' => 'quuz',
                ],
                [
                    Map::create([
                        'lorem' => 'ipsum',
                        'baz' => 'dolor',
                        'quux' => 'sit',
                        'amet' => 123,
                    ]),
                    [
                        'a' => 'b',
                        'c' => 'd',
                        'foo' => 'e',
                        'baz' => 'qux',
                    ],
                ],
                [
                    'baz' => 'qux',
                ],
            ],
        ];
    }

    function testShouldItersectKeysUsingCustomComparator()
    {
        $map = Map::create([
            'foo' => 'bar',
            'baz' => 'qux',
            'quux' => 'quuz',
        ]);

        $keyIntersection = $map->uintersectKeys(
            function ($a, $b) {
                return trim($a) <=> trim($b);
            },
            [
                ' foo ' => 123,
                ' bar ' => 456,
                ' baz ' => 789,
            ],
            Map::create([
                ' baz  ' => 'test',
                '  foo ' => 'dummy',
                'quux ' => 'example',
            ])
        );

        $this->assertMap(
            [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
            $keyIntersection,
            $map
        );
    }

    /**
     * @dataProvider providePairsToDiff
     */
    function testShouldDiff(array $pairs, array $iterables, array $expectedDiff)
    {
        $map = Map::create($pairs);

        $diff = $map->diff(...$iterables);

        $this->assertMap($expectedDiff, $diff, $map);
    }

    function providePairsToDiff()
    {
        return [
            // pairs, iterables, expectedDiff
            [
                ['a' => 'foo', 'b' => 'bar', 'c' => 'baz'],
                [['a' => 'foo', 'c' => 'baz', 'd' => 'qux']],
                ['b' => 'bar'],
            ],
            [
                ['a' => 'foo', 'b' => 'bar', 'c' => 'baz', 'd' => 'qux'],
                [['a' => 'bar', 'b' => 'bar', 'c' => 'qux'], Map::create(['c' => 'baz', 'd' => 'quux'])],
                ['a' => 'foo', 'd' => 'qux'],
            ],
        ];
    }

    function testShouldDiffUsingCustomComparator()
    {
        $map = Map::create([
            'a' => ['id' => 1, 'value' => 'one'],
            'b' => ['id' => 2, 'value' => 'two'],
            'c' => ['id' => 3, 'value' => 'three'],
            'x' => ['id' => 1, 'value' => 'one'],
        ]);

        $this->assertMap(
            [
                'c' => ['id' => 3, 'value' => 'three'],
            ],
            $map->udiff(
                function (array $a, array $b) {
                    return $a['id'] <=> $b['id'];
                },
                [
                    'b' => ['id' => 2, 'value' => 'bar'],
                    'd' => ['id' => 4, 'value' => 'qux'],
                ],
                Map::create([
                    'x' => ['id' => 1, 'value' => 'zero'],
                    'a' => ['id' => 1, 'value' => 'also one'],
                    'e' => ['id' => 5, 'value' => 'quux'],
                ])
            )
        );
    }

    /**
     * @dataProvider providePairsForKeyDiff
     */
    function testShouldDiffKeys(array $pairs, array $iterables, array $expectedDiff)
    {
        $map = Map::create($pairs);

        $diff = $map->diffKeys(...$iterables);

        $this->assertMap($expectedDiff, $diff, $map);
    }

    function providePairsForKeyDiff()
    {
        return [
            // pairs, iterables, expectedDiff
            [
                [
                    'foo' => 123,
                    'bar' => 456,
                    'baz' => 789,
                ],
                [
                    [
                        'foo' => 'bar',
                        'baz' => 'qux',
                        'quux' => 'quuz',
                    ],
                ],
                [
                    'bar' => 456,
                ],
            ],
            [
                [
                    'foo' => 123,
                    'bar' => 456,
                    'baz' => 789,
                    'qux' => 'test',
                ],
                [
                    [
                        'foo' => 'bar',
                        'quux' => 'quuz',
                    ],
                    Map::create([
                        'baz' => 'qux',
                        'quux' => 'quuz',
                    ]),
                ],
                [
                    'bar' => 456,
                    'qux' => 'test',
                ],
            ],
        ];
    }

    function testShouldDiffKeysUsingCustomComparator()
    {
        $map = Map::create([
            'foo' => 123,
            'bar' => 456,
            'baz' => 789,
            'qux' => 'test',
        ]);

        $keyDiff = $map->udiffKeys(
            function ($a, $b) {
                return trim($a) <=> trim($b);
            },
            [
                '  bar ' => 'lorem',
                ' quux ' => 'ipsum',

            ],
            Map::create([
                ' qux  ' => 'dolor',
                'quux' => 'quuz',
            ])
        );

        $this->assertMap(
            [
                'foo' => 123,
                'baz' => 789,
            ],
            $keyDiff,
            $map
        );
    }

    /**
     * @dataProvider providePairsToSort
     */
    function testShouldSort(array $unsortedPairs, array $expectedSortedPairs, int $flags = SORT_REGULAR)
    {
        $map = Map::create($unsortedPairs);

        $sorted = $map->sort($flags);

        $this->assertMap($expectedSortedPairs, $sorted, $map);

        $reverseSorted = $map->sort($flags, true);

        $this->assertMap(array_reverse($expectedSortedPairs, true), $reverseSorted, $map);
    }

    function providePairsToSort()
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
        $map = Map::create([1, 2, 3, 4]);

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
        $map = Map::create($unsortedPairs);

        $sorted = $map->ksort($flags);

        $this->assertMap($expectedSortedPairs, $sorted, $map);

        $reverseSorted = $map->ksort($flags, true);

        $this->assertMap(array_reverse($expectedSortedPairs, true), $reverseSorted, $map);
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
        $map = Map::create(['a' => 1, 'b' => 2, 'c' => 3]);

        $comparator = function ($a, $b) {
            return ($a <=> $b) * -1;
        };

        $this->assertMap(['c' => 3, 'b' => 2, 'a' => 1], $map->uksort($comparator));
    }

    function testShouldCount()
    {
        $this->assertSame(0, (Map::create())->count());
        $this->assertSame(3, $this->getExampleMap()->count());
    }

    /**
     * @dataProvider provideKeysToAccess
     */
    function testShouldReadAsArray(array $pairs, $key, bool $exists, $expectedValue = null)
    {
        $map = Map::create($pairs);

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
        $this->assertMap([], $map->intersectKeys());
        $this->assertMap([], $map->uintersectKeys($callback));
        $this->assertMap([], $map->diff());
        $this->assertMap([], $map->udiff($callback));
        $this->assertMap([], $map->diffKeys());
        $this->assertMap([], $map->udiffKeys($callback));
    }

    function testEmptyMapShouldShortCircuit()
    {
        $map = Map::create();

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
        $this->assertMap([], $map->intersectKeys(['value']));
        $this->assertMap([], $map->uintersectKeys($callback, ['value']));
        $this->assertMap([], $map->diff(['value']));
        $this->assertMap([], $map->udiff($callback, ['value']));
        $this->assertMap([], $map->diffKeys(['value']));
        $this->assertMap([], $map->udiffKeys($callback, ['value']));
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
        return Map::create($this->getExamplePairs());
    }

    private function assertMap(array $expectedPairs, Map $actual, ?Map $expectedNotSameAs = null): void
    {
        $this->assertSame($expectedPairs, $actual->toArray());
        $this->assertNotSame($expectedNotSameAs, $actual);
    }
}
