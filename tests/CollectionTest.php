<?php declare(strict_types=1);

namespace Kuria\Collections;

use Kuria\DevMeta\Test;

class CollectionTest extends Test
{
    /**
     * @dataProvider provideValuesForCreate
     */
    function testShouldCreateCollection(?iterable $values, array $expectedValues)
    {
        $this->assertCollection($expectedValues, Collection::create($values));

        if ($values !== null) {
            $c = Collection::create();
            $c->setValues($values);

            $this->assertCollection($expectedValues, $c);
        }
    }

    function provideValuesForCreate()
    {
        $arrayWithCustomKeys = ['foo' => 'one', 'bar' => 'two', 'baz' => 'three'];
        $arrayWithCustomIntKeys = [3 => 1, 4 => 2, 5 => 3];

        return [
            // values, expectedValues
            'should accept null' => [null, []],
            'should accept empty array' => [[], []],
            'should accept empty traversable' => [Collection::create(), []],
            'should accept non-empty array' => [[1, 2, 3], [1, 2, 3]],
            'should accept non-empty traversable' => [Collection::collect(1, 2, 3), [1, 2, 3]],
            'should discard keys' => [$arrayWithCustomKeys, ['one', 'two', 'three']],
            'should discard integer keys' => [$arrayWithCustomIntKeys, [1, 2, 3]],
            'should discard empty key' => [['' => null], [null]],
            'should discard keys from traversable' => [Map::create($arrayWithCustomKeys), ['one', 'two', 'three']],
            'should discard integer keys from traversable' => [Map::create($arrayWithCustomIntKeys), [1, 2, 3]],
        ];
    }

    function testShouldCreateCollectionFromVarargs()
    {
        $this->assertCollection([], Collection::collect());
        $this->assertCollection([1, 2, 3], Collection::collect(1, 2, 3));
        $this->assertCollection([['foo'], ['bar', 'baz'], ['qux']], Collection::collect(['foo'], ['bar', 'baz'], ['qux']));
    }

    /**
     * @dataProvider provideFillValues
     */
    function testShouldFill($value, int $count, array $expectedValues)
    {
        $c = Collection::fill($value, $count);

        $this->assertCollection($expectedValues, $c);
    }

    function provideFillValues()
    {
        return [
            // value, count, expectedValues
            ['x', -1, []],
            ['foo', 0, []],
            ['bar', 3, ['bar', 'bar', 'bar']],
            [123, 1, [123]],
        ];
    }

    /**
     * @dataProvider provideExplodeValues
     */
    function testShouldExplode(array $expectedValues, ...$args)
    {
        $this->assertCollection($expectedValues, Collection::explode(...$args));
    }

    function provideExplodeValues()
    {
        $string = 'foo,bar,baz';

        return [
            // expectedValues, string, delimiter, [limit]
            [[''], '', ','],
            [['foo'], 'foo', ','],
            [['foo', 'bar', 'baz'], $string, ','],
            [['foo', 'bar,baz'], $string, ',', 2],
            [[$string], $string, ',', 1],
            [[$string], $string, ',', 0],
            [['foo', 'bar'], $string, ',', -1],
        ];
    }

    function testShouldCheckIfCollectionIsEmpty()
    {
        $this->assertTrue((Collection::create())->isEmpty());
        $this->assertFalse($this->getExampleCollection()->isEmpty());
    }

    /**
     * @dataProvider provideIndexesToAccess
     */
    function testShouldCheckExistenceAndGetValue(array $values, int $index, bool $exists, $expectedValue = null)
    {
        $c = Collection::create($values);

        $this->assertSame($exists, $c->has($index));
        $this->assertSame($expectedValue, $c->get($index));
    }

    function provideIndexesToAccess()
    {
        $values = $this->getExampleValues();
        $values[] = null;

        return [
            // values, index, exists, expectedValue
            [$values, -2, false, null],
            [$values, -1, false, null],
            [$values, 0, true, 'foo'],
            [$values, 1, true, 'bar'],
            [$values, 2, true, 'baz'],
            [$values, 3, true, null],
            [$values, 4, false, null],
            [$values, 5, false, null],
        ];
    }

    /**
     * @dataProvider provideValuesAndIndexes
     */
    function testShouldCheckIfValueExistsAndFindIt(array $values, $value, bool $strict, ?int $expectedIndex)
    {
        $c = Collection::create($values);

        $this->assertSame($expectedIndex !== null, $c->contains($value, $strict));
        $this->assertSame($expectedIndex, $c->find($value, $strict));
    }

    function provideValuesAndIndexes()
    {
        $object = (object) ['property' => 'value'];

        $values = [
            'foo',
            'bar',
            123,
            '456',
        ];

        $objectValues = [
            new \stdClass(),
            $object,
        ];

        $arrayValues = [
            ['key' => 111],
            ['key' => 123],
        ];

        return [
            // values, value, strict, expectedIndex
            [$values, 'foo', true, 0],
            [$values, 'bar', true, 1],
            [$values, 123, true, 2],
            [$values, '123', true, null],
            [$values, '123', false, 2],
            [$values, '456', true, 3],
            [$values, 456, true, null],
            [$values, 456, false, 3],
            [$values, 'baz', true, null],
            [$values, 'baz', false, null],
            [$objectValues, $object, true, 1],
            [$objectValues, clone $object, true, null],
            [$objectValues, clone $object, false, 1],
            [$arrayValues, ['key' => 123], true, 1],
            [$arrayValues, ['key' => '123'], true, null],
            [$arrayValues, ['key' => '123'], false, 1],
        ];
    }

    /**
     * @dataProvider provideFirstAndLastValues
     */
    function testShouldGetFirstAndLast(array $values, $expectedFirst, $expectedLast)
    {
        $c = Collection::create($values);

        $this->assertSame($expectedFirst, $c->first());
        $this->assertSame($expectedLast, $c->last());
    }

    function provideFirstAndLastValues()
    {
        return [
            // values, expectedFirst, expectedLast
            [[1, 2, 3], 1, 3],
            [[1, 2], 1, 2],
            [['foo'], 'foo', 'foo'],
            [[], null, null],
        ];
    }

    /**
     * @dataProvider provideValues
     */
    function testShouldConvertToArrayAndGetIndexes(array $values)
    {
        $c = Collection::create($values);

        $this->assertSame($values, $c->toArray());
        $this->assertSame(array_keys($values), $c->indexes());
    }

    function provideValues()
    {
        return [
            // values
            [$this->getExampleValues()],
            [[]],
        ];
    }

    /**
     * @dataProvider provideSliceOffsets
     */
    function testShouldSlice(int $index, ?int $length, array $expectedValues)
    {
        $c = $this->getExampleCollection();
        $slice = $c->slice($index, $length);

        $this->assertCollection($expectedValues, $slice, $c);
    }

    function provideSliceOffsets()
    {
        return [
            // index, length, expectedValues
            [0, null, ['foo', 'bar', 'baz']],
            [1, null, ['bar', 'baz']],
            [2, null, ['baz']],
            [3, null, []],
            [0, 0, []],
            [0, 1, ['foo']],
            [0, 2, ['foo', 'bar']],
            [0, 3, ['foo', 'bar', 'baz']],
            [0, 4, ['foo', 'bar', 'baz']],
            [1, 1, ['bar']],
            [1, 2, ['bar', 'baz']],
            [1, 3, ['bar', 'baz']],
            [2, 1, ['baz']],
            [2, 2, ['baz']],
            [-1, null, ['baz']],
            [-2, null, ['bar', 'baz']],
            [-3, null, ['foo', 'bar', 'baz']],
            [-4, null, ['foo', 'bar', 'baz']],
            [-1, 1, ['baz']],
            [-2, 1, ['bar']],
            [-3, 1, ['foo']],
            [-1, 2, ['baz']],
            [-2, 2, ['bar', 'baz']],
            [-3, 2, ['foo', 'bar']],
            [0, -1, ['foo', 'bar']],
            [0, -2, ['foo']],
            [0, -3, []],
            [1, -1, ['bar']],
            [2, -2, []],
            [3, -3, []],
            [-1, -1, []],
            [-1, -2, []],
            [-2, -1, ['bar']],
            [-2, -2, []],
            [-2, -3, []],
            [-3, -2, ['foo']],
        ];
    }

    function testShouldReplace()
    {
        $c = $this->getExampleCollection();

        $c->replace(0, 'one');
        $c->replace(1, 'two');
        $c->replace(2, null);

        $this->assertCollection(['one', 'two', null], $c);
    }

    function testReplaceShouldThrowExceptionIfCollectionIsEmpty()
    {
        $c = Collection::create();

        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage('Cannot replace value at index 0 because it does not exist (the collection is empty)');

        $c->replace(0, 'value');
    }

    function testReplaceShouldThrowExceptionIfIndexIsInvalid()
    {
        $c = $this->getExampleCollection();

        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage('Cannot replace value at index 10 because it does not exist (valid indexes are 0 to 2)');

        $c->replace(10, 'value');
    }

    function testShouldPushAndPop()
    {
        $c = Collection::create();

        $c->push('one');
        $this->assertCollection(['one'], $c);

        $c->push('two', 'three');
        $this->assertCollection(['one', 'two', 'three'], $c);

        $this->assertSame('three', $c->pop());
        $this->assertCollection(['one', 'two'], $c);

        $this->assertSame('two', $c->pop());
        $this->assertCollection(['one'], $c);

        $this->assertSame('one', $c->pop());
        $this->assertCollection([], $c);
    }

    function testShiftAndUnshift()
    {
        $c = Collection::create();

        $c->unshift('c');
        $this->assertCollection(['c'], $c);

        $c->unshift('a', 'b');
        $this->assertCollection(['a', 'b', 'c'], $c);

        $this->assertSame('a', $c->shift());
        $this->assertCollection(['b', 'c'], $c);

        $this->assertSame('b', $c->shift());
        $this->assertCollection(['c'], $c);

        $this->assertSame('c', $c->shift());
        $this->assertCollection([], $c);
    }

    function testShouldInsert()
    {
        $c = Collection::create();

        $c->insert(0); // no values given
        $this->assertCollection([], $c);

        $c->insert(0, 'foo');
        $this->assertCollection(['foo'], $c);

        $c->insert(0, 'bar', 'baz');
        $this->assertCollection(['bar', 'baz', 'foo'], $c);

        $c->insert(1, 'qux');
        $this->assertCollection(['bar', 'qux', 'baz', 'foo'], $c);

        $c->insert(999, 'quux');
        $this->assertCollection(['bar', 'qux', 'baz', 'foo', 'quux'], $c);

        $c->insert(-1, 'corge');
        $this->assertCollection(['bar', 'qux', 'baz', 'foo', 'corge', 'quux'], $c);

        $c->insert(-2, 'bablbam', 'gudbaj');
        $this->assertCollection(['bar', 'qux', 'baz', 'foo', 'bablbam', 'gudbaj', 'corge', 'quux'], $c);
    }

    /**
     * @dataProvider provideValuesToPad
     */
    function testShouldPad(array $values, int $length, $value, array $expectedPaddedValues)
    {
        $c = Collection::create($values);
        $c->pad($length, $value);

        $this->assertCollection($expectedPaddedValues, $c);
    }

    function provideValuesToPad()
    {
        return [
            // values, length, value, expectedPaddedValues
            [
                [],
                0,
                'dummy',
                [],
            ],
            [
                [],
                3,
                'foo',
                ['foo', 'foo', 'foo'],
            ],
            [
                [],
                -3,
                'foo',
                ['foo', 'foo', 'foo'],
            ],
            [
                [1, 2, 3],
                5,
                99,
                [1, 2, 3, 99, 99],
            ],
            [
                [1, 2, 3],
                -5,
                99,
                [99, 99, 1, 2, 3],
            ],
            [
                [1, 2, 3, 4, 5],
                5,
                99,
                [1, 2, 3, 4, 5],
            ],
            [
                [1, 2, 3, 4, 5],
                -5,
                99,
                [1, 2, 3, 4, 5],
            ],
        ];
    }

    function testShouldRemove()
    {
        $c = $this->getExampleCollection();

        $c->remove(2);
        $this->assertCollection(['foo', 'bar'], $c);

        $c->remove(2); // nonexistent
        $this->assertCollection(['foo', 'bar'], $c);

        $c->remove(0);
        $this->assertCollection(['bar'], $c);

        $c->remove(0);
        $this->assertCollection([], $c);

        $c->remove(0); // nonexistent
        $this->assertCollection([], $c);
    }

    function testShouldRemoveMultiple()
    {
        $c = $this->getExampleCollection();
        $c->push('qux');

        $c->remove(0, 2);
        $this->assertCollection(['bar', 'qux'], $c);

        $c->remove(0, 0, 0);
        $this->assertCollection(['qux'], $c);

        $c->remove(1, 2, 3); // nonexistent
        $this->assertCollection(['qux'], $c);

        $c->remove(0);
        $this->assertCollection([], $c);

        $c->remove(0, 1, 2); // // nonexistent
        $this->assertCollection([], $c);
    }

    function testShouldClear()
    {
        $c = $this->getExampleCollection();
        $c->clear();

        $this->assertCollection([], $c);
    }

    function testShouldSplice()
    {
        $c = $this->getExampleCollection();

        $c->splice(1, 2, ['bar-2', 'baz-2']);
        $this->assertCollection(['foo', 'bar-2', 'baz-2'], $c);

        $c->splice(0, 0, ['qux']);
        $this->assertCollection(['qux', 'foo', 'bar-2', 'baz-2'], $c);

        $c->splice(-4, 3, Collection::collect('foo', 'bar'));
        $this->assertCollection(['foo', 'bar', 'baz-2'], $c);

        $c->splice(-2, -1, null);
        $this->assertCollection(['foo', 'baz-2'], $c);

        $c->splice(99, 99, []);
        $this->assertCollection(['foo', 'baz-2'], $c);

        $c->splice(0);
        $this->assertCollection([], $c);
    }

    /**
     * @dataProvider provideValuesToSum
     */
    function testShouldSum(array $values, $expectedResult)
    {
        $this->assertSame($expectedResult, (Collection::create($values))->sum());
    }

    function provideValuesToSum()
    {
        return [
            // values, expectedResult
            [[1, 2, 3], 6],
            [[1.2, 3.4], 4.6],
            [['5', '10'], 15],
            [[10], 10],
            [[], 0],
        ];
    }

    /**
     * @dataProvider provideValuesToProduct
     */
    function testShouldProduct(array $values, $expectedResult)
    {
        $this->assertSame($expectedResult, (Collection::create($values))->product());
    }

    function provideValuesToProduct()
    {
        return [
            // values, expectedResult
            [[4, 5, 6], 120],
            [[3.14, 2.8], 8.792],
            [['8', '10'], 80],
            [[20], 20],
            [[], 1],
        ];
    }

    /**
     * @dataProvider provideValuesToImplode
     */
    function testShouldImplode(array $values, string $delimiter, string $expectedResult)
    {
        $this->assertSame($expectedResult, (Collection::create($values))->implode($delimiter));
    }

    function provideValuesToImplode()
    {
        return [
            // values, delimiter, expectedResult
            [[], '.', ''],
            [[123], '-', '123'],
            [['foo', 'bar'], '.', 'foo.bar'],
        ];
    }

    /**
     * @dataProvider provideValuesToReduce
     */
    function testShouldReduce(array $values, callable $reducer, $initial, $expectedResult)
    {
        $this->assertSame($expectedResult, (Collection::create($values))->reduce($reducer, $initial));
    }

    function provideValuesToReduce()
    {
        $reducer = function ($result, $value) {
            return $result + $value;
        };

        return [
            // values, reducer, initial, expectedResult
            [[], $reducer, null, null],
            [[], $reducer, -1, -1],
            [[1, 2, 3], $reducer, -1, 5],
        ];
    }

    function testShouldReverse()
    {
        $c = $this->getExampleCollection();
        $reversed = $c->reverse();

        $this->assertCollection(['baz', 'bar', 'foo'], $reversed, $c);
    }

    /**
     * @dataProvider provideValuesToChunk
     */
    function testShouldChunk(array $values, int $size, array $expectedChunks)
    {
        $c = Collection::create($values);

        $chunks = $c->chunk($size);

        $this->assertCount(count($expectedChunks), $chunks);

        foreach ($expectedChunks as $index => $expectedChunk) {
            $this->assertArrayHasKey($index, $chunks);
            $this->assertInstanceOf(Collection::class, $chunks[$index]);
            $this->assertCollection($expectedChunk, $chunks[$index], $c);
        }
    }

    function provideValuesToChunk()
    {
        return [
            // values, size, expectedChunks
            [[], 1, []],
            [[1, 2], 10, [[1, 2]]],
            [$this->getExampleValues(), 2, [['foo', 'bar'], ['baz']]],
        ];
    }

    /**
     * @dataProvider provideValuesToSplit
     */
    function testShouldSplit(array $values, int $number, array $expectedParts)
    {
        $c = Collection::create($values);

        $parts = $c->split($number);

        $this->assertCount(count($expectedParts), $parts);

        foreach ($expectedParts as $index => $expectedPart) {
            $this->assertArrayHasKey($index, $parts);
            $this->assertInstanceOf(Collection::class, $parts[$index]);
            $this->assertCollection($expectedPart, $parts[$index], $c);
        }
    }

    function provideValuesToSplit()
    {
        return [
            // values, number, expectedParts
            [[1, 2], -1, []],
            [[3, 4], 0, []],
            [[], 3, []],
            [$this->getExampleValues(), 2, [['foo', 'bar'], ['baz']]],
            [[1, 2, 3], 3, [[1], [2], [3]]],
        ];
    }

    /**
     * @dataProvider provideNonUniqueValues
     */
    function testShouldGetUniqueValues(array $values, array $expectedUniqueValues)
    {
        $c = Collection::create($values);

        $unique = $c->unique();

        $this->assertCollection($expectedUniqueValues, $unique, $c);
    }

    function provideNonUniqueValues()
    {
        $object = (object) ['property' => 123];
        $equalObject = (object) ['property' => '123'];
        $differentObject = (object) ['property' => 'value'];
        $array = [1, 2, 3];
        $equalArray = ['1', '2', '3'];
        $differentArray = ['foo', 'bar', 'baz'];

        return [
            // values, expectedUniqueValues
            [[1, 2, 2, 3, 3, 4], [1, 2, 3, 4]],
            [['foo', 'foo', 'bar', 'BAR', 'baz'], ['foo', 'bar', 'BAR', 'baz']],
            [[$object, $equalObject, $differentObject], [$object, $differentObject]],
            [[$array, $equalArray, $differentArray], [$array, $differentArray]],
        ];
    }

    function testShouldShuffle()
    {
        $c = $this->getExampleCollection();

        $shuffled = $c->shuffle();

        $this->assertTrue($c->contains('foo'));
        $this->assertTrue($c->contains('bar'));
        $this->assertTrue($c->contains('baz'));
        $this->assertNotSame($c, $shuffled); // should return new instance
    }

    /**
     * @dataProvider provideRandomCounts
     */
    function testShouldGetRandomValues(int $count, int $expectedResultSize)
    {
        $this->assertCount($expectedResultSize, $this->getExampleCollection()->random($count));
    }

    function provideRandomCounts()
    {
        return [
            // count, expectedResultSize
            [-1, 0],
            [0, 0],
            [1, 1],
            [2, 2],
            [3, 3],
            [4, 3],
        ];
    }

    /**
     * @dataProvider provideValuesToColumn
     */
    function testShouldGetColumn(array $values, $key, array $expectedColumn)
    {
        $c = Collection::create($values);
        $column = $c->column($key);

        $this->assertCollection($expectedColumn, $column, $c);
    }

    function provideValuesToColumn()
    {
        $values = [
            ['foo' => 'bar', 'baz' => 'qux'],
            (object) ['foo' => 'a'],
            new \stdClass(),
            'not_an_object',
            123456,
        ];

        return [
            // values, key, expectedColumn
            [[], 'dummy', []],
            [$values, 'foo', ['bar', 'a']],
            [$values, 'baz', ['qux']],
            [$values, 'nonexistent', []],
        ];
    }

    /**
     * @dataProvider provideValuesForColumnMapping
     */
    function testShouldMapColumn(array $values, $indexKey, $valueKey, array $expectedPairs)
    {
        $this->assertSame($expectedPairs, Collection::create($values)->mapColumn($indexKey, $valueKey)->toArray());
    }

    function provideValuesForColumnMapping()
    {
        $values = [
            ['key' => 'lorem', 'value' => 'ipsum'],
            (object) ['key' => 'dolor', 'value' => 'sit'],
            new \stdClass(),
            'not_an_object',
            123456,
        ];

        return [
            // values, indexKey, valueKey, expectedPairs
            [
                [],
                'key',
                'value',
                [],
            ],
            [
                $values,
                'key',
                'value',
                [
                    'lorem' => 'ipsum',
                    'dolor' => 'sit',
                ],
            ],
            [
                $values,
                'nonexistent',
                'nonexistent',
                [],
            ],
        ];
    }

    function testShouldFilter()
    {
        $c = $this->getExampleCollection();

        $filtered = $c->filter(function ($value) {
            return $value === 'foo' || $value === 'bar';
        });

        $this->assertCollection(['foo', 'bar'], $filtered, $c);
    }

    function testShouldApplyCallback()
    {
        $c = $this->getExampleCollection();

        $mapped = $c->apply(function ($value) {
            return "{$value}-2";
        });

        $this->assertCollection(['foo-2', 'bar-2', 'baz-2'], $mapped, $c);
    }

    function testShouldMap()
    {
        $c = $this->getExampleCollection();

        $map = $c->map(function ($value) {
            return "key.{$value}";
        });

        $this->assertLooselyIdentical(
            Map::create([
                'key.foo' => 'foo',
                'key.bar' => 'bar',
                'key.baz' => 'baz',
            ]),
            $map
        );
    }

    /**
     * @dataProvider provideValuesToMerge
     */
    function testShouldMerge(array $values, array $iterables, array $expectedResult)
    {
        $c = Collection::create($values);

        $result = $c->merge(...$iterables);

        $this->assertCollection($expectedResult, $result);
    }

    function provideValuesToMerge()
    {
        return [
            // values, iterables, expectedResult
            [
                [],
                [],
                [],
            ],
            [
                [],
                [[1, 2, 3]],
                [1, 2, 3],
            ],
            [
                [1, 2, 3],
                [[3, 4, 5]],
                [1, 2, 3, 3, 4, 5],
            ],
            [
                [1, 2, 3],
                [[3, 4, 5], Collection::collect(5, 6, 7)],
                [1, 2, 3, 3, 4, 5, 5, 6, 7],
            ],
        ];
    }

    /**
     * @dataProvider provideValuesToIntersect
     */
    function testShouldIntersect(array $values, array $iterables, array $expectedIntersection)
    {
        $c = Collection::create($values);

        $intersection = $c->intersect(...$iterables);

        $this->assertCollection($expectedIntersection, $intersection, $c);
    }

    function provideValuesToIntersect()
    {
        return [
            // values, iterables, expectedIntersection
            [['foo', 'bar', 'baz'], [['foo', 'baz', 'qux']], ['foo', 'baz']],
            [['foo', 'bar', 'baz'], [['bar', 'baz', 'qux'], Collection::collect('foo', 'bar')], ['bar']],
        ];
    }

    function testShouldIntersectWithCustomComparator()
    {
        $c = Collection::create([
            ['id' => 1, 'value' => 'one'],
            ['id' => 2, 'value' => 'two'],
            ['id' => 3, 'value' => 'three'],
        ]);

        $comparator = function (array $a, array $b) {
            return $a['id'] <=> $b['id'];
        };

        $this->assertCollection(
            [
                ['id' => 2, 'value' => 'two'],
            ],
            $c->uintersect(
                $comparator,
                [
                    ['id' => 2, 'value' => 'bar'],
                    ['id' => 4, 'value' => 'qux'],
                ],
                [
                    ['id' => 0, 'value' => 'zero'],
                    ['id' => 2, 'value' => 'also two'],
                    ['id' => 5, 'value' => 'quux'],
                ]
            )
        );
    }

    /**
     * @dataProvider provideValuesToDiff
     */
    function testShouldDiff(array $values, array $iterables, array $expectedDiff)
    {
        $c = Collection::create($values);

        $diff = $c->diff(...$iterables);

        $this->assertCollection($expectedDiff, $diff, $c);
    }

    function provideValuesToDiff()
    {
        return [
            // values, iterables, expectedDiff
            [['foo', 'bar', 'baz'], [['foo', 'baz', 'qux']], ['bar']],
            [['foo', 'bar', 'baz', 'qux'], [['bar', 'lorem', 'ipsum'], Collection::collect('baz', 'quux')], ['foo', 'qux']],
        ];
    }

    function testShouldDiffWithCustomComparator()
    {
        $c = Collection::create([
            ['id' => 1, 'value' => 'one'],
            ['id' => 2, 'value' => 'two'],
            ['id' => 3, 'value' => 'three'],
        ]);

        $comparator = function (array $a, array $b) {
            return $a['id'] <=> $b['id'];
        };

        $this->assertCollection(
            [
                ['id' => 3, 'value' => 'three'],
            ],
            $c->udiff(
                $comparator,
                [
                    ['id' => 2, 'value' => 'bar'],
                    ['id' => 4, 'value' => 'qux'],
                ],
                [
                    ['id' => 0, 'value' => 'zero'],
                    ['id' => 1, 'value' => 'also one'],
                    ['id' => 5, 'value' => 'quux'],
                ]
            )
        );
    }

    /**
     * @dataProvider provideValuesToSort
     */
    function testShouldSort(array $unsortedValues, array $expectedSortedValues, int $flags = SORT_REGULAR)
    {
        $c = Collection::create($unsortedValues);

        $sorted = $c->sort($flags);

        $this->assertCollection($expectedSortedValues, $sorted, $c);

        $reverseSorted = $c->sort($flags, true);

        $this->assertCollection(array_reverse($expectedSortedValues), $reverseSorted, $c);
    }

    function provideValuesToSort()
    {
        return [
            // unsortedValues, expectedSortedValues, [flags]
            [[1, 6, 9, 5, 2, 4, 10, 7, 3, 8], [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]],
            [['1', '6', '9', '5', '2', '4', '10', '7', '3', '8'], ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10']],
            [['foo', 'bar', 'baz'], ['bar', 'baz', 'foo']],
            [['foo.5', 'foo.10', 'foo.1'], ['foo.1', 'foo.10', 'foo.5']],
            [['foo.5', 'foo.10', 'foo.1'], ['foo.1', 'foo.5', 'foo.10'], SORT_NATURAL],
            [['bar.5', 'BAR.2', 'bar.1'], ['BAR.2', 'bar.1', 'bar.5'], SORT_NATURAL],
            [['bar.5', 'BAR.2', 'bar.1'], ['bar.1', 'BAR.2', 'bar.5'], SORT_NATURAL | SORT_FLAG_CASE],
        ];
    }

    function testShouldSortWithCustomComparator()
    {
        $c = Collection::collect(1, 2, 3, 4);

        $comparator = function ($a, $b) {
            return ($a <=> $b) * -1;
        };

        $this->assertCollection([4, 3, 2, 1], $c->usort($comparator));
    }

    function testShouldCount()
    {
        $this->assertSame(0, (Collection::create())->count());
        $this->assertSame(3, $this->getExampleCollection()->count());
    }

    /**
     * @dataProvider provideIndexesToAccess
     */
    function testShouldReadAsArray(array $values, int $index, bool $exists, $expectedValue)
    {
        $c = Collection::create($values);

        $this->assertSame($exists, isset($c[$index]));
        $this->assertSame($expectedValue, $c[$index]);
    }

    function testShouldWriteAsArray()
    {
        $c = $this->getExampleCollection();

        // offsetSet()
        $c[0] = 'new foo';
        $c[1] = 'new bar';
        $c[2] = 'new baz';
        $c[] = 'qux';

        $this->assertCollection(['new foo', 'new bar', 'new baz', 'qux'], $c);

        $e = null;
        try {
            $c[4] = 'quux';
        } catch (\OutOfBoundsException $e) {
            $this->assertSame(
                'Cannot replace value at index 4 because it does not exist (valid indexes are 0 to 3)',
                $e->getMessage()
            );
        }

        $this->assertNotNull($e);

        // offsetUnset()
        unset($c[1], $c[1]);

        $this->assertCollection(['new foo', 'qux'], $c);
    }

    function testShouldGetIterator()
    {
        $this->assertSame(
            $this->getExampleValues(),
            iterator_to_array($this->getExampleCollection()->getIterator())
        );
    }

    function testBlankVarargsShouldShortCircuit()
    {
        $collection = $this->getExampleCollection();
        $values = $collection->toArray();

        $callback = function () {
            $this->fail('Callback should not be called when there are no varargs');
        };

        // no-op
        $collection->push();
        $this->assertCollection($values, $collection);

        $collection->unshift();
        $this->assertCollection($values, $collection);

        $collection->insert(1);
        $this->assertCollection($values, $collection);

        $collection->remove();
        $this->assertCollection($values, $collection);

        // empty result
        $this->assertCollection([], $collection->intersect());
        $this->assertCollection([], $collection->uintersect($callback));
        $this->assertCollection([], $collection->diff());
        $this->assertCollection([], $collection->udiff($callback));
    }

    function testEmptyCollectionShouldShortCircuit()
    {
        $c = Collection::create();

        $callback = function () {
            $this->fail('Callback should not be called when the collection is empty');
        };

        $this->assertCollection([], $c->unique());
        $this->assertCollection([], $c->filter($callback));
        $this->assertCollection([], $c->apply($callback));
        $this->assertCollection([], $c->intersect([1, 2, 3]));
        $this->assertCollection([], $c->uintersect($callback, [1, 2, 3]));
        $this->assertCollection([], $c->diff([1, 2, 3]));
        $this->assertCollection([], $c->udiff($callback, [1, 2, 3]));
        $this->assertCollection([], $c->sort());
        $this->assertCollection([], $c->usort($callback));
    }

    private function getExampleValues(): array
    {
        return ['foo', 'bar', 'baz'];
    }

    private function getExampleCollection(): Collection
    {
        return Collection::create($this->getExampleValues());
    }

    private function assertCollection(array $expectedValues, Collection $actual, ?Collection $expectedNotSameAs = null): void
    {
        $this->assertSame($expectedValues, $actual->toArray());
        $this->assertSame(array_keys($expectedValues), $actual->indexes());
        $this->assertNotSame($expectedNotSameAs, $actual);
    }
}
