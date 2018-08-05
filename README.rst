Collections
###########

Object-oriented collection structures.

.. image:: https://travis-ci.org/kuria/collections.svg?branch=master
   :target: https://travis-ci.org/kuria/collections

.. contents::


Features
********

- ``Collection`` - list of values with sequential integer indexes
- ``Map`` - list of key value pairs


Requirements
************

- PHP 7.1+


Purpose and recommendations
***************************

This library exists because PHP does not provide these structures by default.

The ``array`` type is both a list and a map. It cannot be passed into functions
and modified directly without references.

By using these structures you can make your *public* API more explicit about what
it expects and returns.


Collection
**********

The ``Collection`` class implements a list of values with sequential integer indexes.

It also implements ``Countable``, ``ArrayAccess`` and ``IteratorAggregate``.


Creating a new collection
=========================

Empty collection
----------------

.. code:: php

   <?php

   use Kuria\Collections\Collection;

   $collection = new Collection();


Using an existing `iterable <http://php.net/manual/en/language.types.iterable.php>`_
------------------------------------------------------------------------------------

.. code:: php

   <?php

   use Kuria\Collections\Collection;

   $collection = new Collection(['foo', 'bar', 'baz']);


Collection method overview
==========================

Refer to doc comments of the respective methods for more information.


Static methods
--------------

- ``fill($value, $count): self`` - create a collection and populate it with repeated values
- ``explode($string, $delimiter, $limit = PHP_INT_MAX): self`` - create a collection by splitting a string


Instance methods
----------------

- ``toArray(): bool`` - get all values as an array
- ``isEmpty(): bool`` - see if the collection is empty
- ``count(): int`` - count values
- ``has($index): bool`` - see if the given index exists
- ``contains($value, $strict = true): bool`` - see if the given value exists
- ``find($value, $strict = true): ?int`` - try to find the first occurence of a value
- ``get($index)`` - get value at the given index
- ``first()`` - get first value
- ``last()`` - get last value
- ``indexes(): array`` - get all indexes
- ``slice($index, $length = null): self`` - extract a slice of the collection
- ``replace($index, $value): void`` - replace a value at the given index
- ``push(...$values): void`` - push one or more values onto the end of the collection
- ``pop()`` - pop a value off the end of the collection
- ``unshift(...$values)`` - prepend one or more values to the beginning of the collection
- ``shift(): void`` - shift a value off the beginning of the collection
- ``insert($index, $value): void`` - insert one or more values at the given index
- ``remove(...$indexes)`` - remove values at the given indexes
- ``clear(): void`` - remove all values
- ``splice($index, $length = null, $replacement = null): void`` - remove or replace a part of the collection
- ``sum(): int|float`` - calculate the sum of all values
- ``product(): int|float`` - calculate the product of all values
- ``implode($delimiter = ''): string`` - join all values using a delimiter
- ``reduce($callback, $initial = null)`` - reduce the collection to a single value
- ``reverse(): self`` - reverse the collection
- ``chunk($size): self[]`` - split the collection into chunks of the given size
- ``split($number): self[]`` - split the collection into the given number of chunks
- ``unique(): self`` - get unique values
- ``shuffle(): self`` - get values in random order
- ``random(): self`` - get N random values from the collection
- ``column($key): self`` - gather values from a property or array index of all object or array values
- ``filter($filter): self`` - filter values using the given callback
- ``apply($callback): self`` - apply the callback to all values
- ``map($mapper): Map`` - convert the collection to a map
- ``intersect(...$others): self`` - compute an intersection with the given iterables
- ``uintersect($comparator, ...$others): self`` - compute an intersection with the given iterables using a custom comparator
- ``diff(...$others): self`` - compute a difference between this collection and the given iterables
- ``udiff($comparator, ...$others): self`` - compute a difference between this collection and the given iterables using a custom comparator
- ``sort($flags = SORT_REGULAR, $reverse = false): self`` - sort the collection
- ``usort($comparator): self`` - sort the collection using a custom comparator


Array access and iteration
==========================

``Collection`` instances can be accessed and iterated as regular arrays.

.. code:: php

   <?php

   use Kuria\Collections\Collection;

   $collection = new Collection();

   // push some values
   $collection[] = 'foo';
   $collection[] = 'bar';
   $collection[] = 'baz';

   // replace a value
   $collection[1] = 'new bar';

   // remove a value
   unset($collection[2]);

   // read values
   echo 'Value at index 1 is ', $collection[1], "\n";
   echo 'Value at index 2 ', isset($collection[2]) ? 'exists' : 'does not exist', "\n";

   // count values
   echo 'There are ', count($collection), ' values in total', "\n";

   // iterate values
   foreach ($collection as $index => $value) {
       echo $index, ': ', $value, "\n";
   }

Output:

::

  Value at index 1 is new bar
  Value at index 2 does not exist
  There are 2 values in total
  0: foo
  1: new bar


Map
***

The ``Map`` class implements a key value map.

It also implements ``Countable``, ``ArrayAccess`` and ``IteratorAggregate``.


Creating a new map
==================

Empty map
---------

.. code:: php

   <?php

   use Kuria\Collections\Map;

   $map = new Map();


Using an existing `iterable <http://php.net/manual/en/language.types.iterable.php>`_
------------------------------------------------------------------------------------

.. code:: php

   <?php

   use Kuria\Collections\Map;

   $collection = new Map(['foo' => 'bar', 'bar' => 'baz']);


Map method overview
===================

Refer to doc comments of the respective methods for more information.

Static methods
--------------

- ``combine($keys, $values): self`` - combine a list of keys and a list of values to create a map


Instance methods
----------------

- ``toArray(): bool`` - get all pairs as an array
- ``isEmpty(): bool`` - see if the map is empty
- ``count(): int`` - count pairs
- ``has($key): bool`` - see if the given key exists
- ``contains($value, $strict = true): bool`` - see if the given value exists
- ``find($value, $strict = true)`` - try to find the first occurence of a value
- ``get($key)`` - get value for the given key
- ``values(): Collection`` - get all values
- ``keys(): Collection`` - get all keys
- ``set($key, $value): void`` - define a pair
- ``add(...$others): void`` - add pairs from other iterables to this map
- ``fill($keys, $value): void`` - fill specific keys with a value
- ``remove(...$keys): void`` - remove pairs with the given keys
- ``clear(): void`` - remove all pairs
- ``reduce($reducer, $initial = null)`` - reduce the map to a single value
- ``flip(): self`` - swap keys and values
- ``shuffle(): self`` - randomize pair order
- ``column($key, $indexKey = null): self`` - gather values from properties or array keys of all object or array values
- ``filter($filter): self`` - filter pairs using the given callback
- ``map($mapper): self`` - remap pairs using the given callback
- ``intersect(...$others): self`` - compute an intersection with the given iterables
- ``uintersect($comparator, ...$others): self`` - compute an intersection with the given iterables using a custom comparator
- ``diff(...$others): self`` - compute a difference between this map and the given iterables
- ``udiff($comparator, ...$others): self`` - compute a difference between this map and the given iterables using a custom comparator
- ``sort($flags = SORT_REGULAR, $reverse = false): self`` - sort the map using its values
- ``usort($comparator): self`` - sort the map using its values and a custom comparator
- ``ksort($flags = SORT_REGULAR, $reverse = false): self`` - sort the map using its keys
- ``uksort(): self`` - sort the map using its keys and a custom comparator


Array access and iteration
==========================

``Map`` instances can be accessed and iterated as regular arrays.

.. code:: php

   <?php

   use Kuria\Collections\Map;

   $map = new Map();

   // add some pairs
   $map['foo'] = 'bar';
   $map['baz'] = 'qux';
   $map['quux'] = 'corge';

   // remove a pair
   unset($map['baz']);

   // read values
   echo 'Value with key "foo" is ', $map['foo'], "\n";
   echo 'Value with key "baz" ', isset($map['baz']) ? 'exists' : 'does not exist', "\n";

   // count pairs
   echo 'There are ', count($map), ' pairs in total', "\n";

   // iterate pairs
   foreach ($map as $key => $value) {
      echo $key, ': ', $value, "\n";
   }

Output:

::

  Value with key "foo" is bar
  Value with key "baz" does not exist
  There are 2 pairs in total
  foo: bar
  quux: corge
