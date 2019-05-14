Collections
###########

Object-oriented collection structures.

.. image:: https://travis-ci.com/kuria/collections.svg?branch=master
   :target: https://travis-ci.com/kuria/collections

.. contents::


Features
********

- ``Collection`` - list of values with sequential integer indexes
- ``Map`` - key-value map


Requirements
************

- PHP 7.1+


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

   $collection = Collection::create();


Using an existing `iterable <http://php.net/manual/en/language.types.iterable.php>`_
------------------------------------------------------------------------------------

.. code:: php

   <?php

   use Kuria\Collections\Collection;

   $collection = Collection::create(['foo', 'bar', 'baz']);


Using varargs
-------------

.. code:: php

   <?php

   use Kuria\Collections\Collection;

   $collection = Collection::collect('foo', 'bar', 'baz');


Collection method overview
==========================

Refer to doc comments of the respective methods for more information.


Static methods
--------------

- ``create($values = null): self`` - create a collection from an iterable
- ``collect(...$values): self`` - create a collection from the passed arguments
- ``fill($value, $count): self`` - create a collection and populate it with repeated values
- ``explode($string, $delimiter, $limit = PHP_INT_MAX): self`` - create a collection by splitting a string


Instance methods
----------------

- ``setValues($values): void`` - replace all values with the given iterable
- ``toArray(): array`` - get all values as an array
- ``isEmpty(): bool`` - see if the collection is empty
- ``count(): int`` - count values
- ``has($index): bool`` - see if the given index exists
- ``contains($value, $strict = true): bool`` - see if the given value exists
- ``find($value, $strict = true): ?int`` - try to find the first occurence of a value
- ``get($index): mixed`` - get value at the given index
- ``first(): mixed`` - get first value
- ``last(): mixed`` - get last value
- ``indexes(): int[]`` - get all indexes
- ``slice($index, $length = null): self`` - extract a slice of the collection
- ``replace($index, $value): void`` - replace a value at the given index
- ``push(...$values): void`` - push one or more values onto the end of the collection
- ``pop(): mixed`` - pop a value off the end of the collection
- ``unshift(...$values): void`` - prepend one or more values to the beginning of the collection
- ``shift(): mixed`` - shift a value off the beginning of the collection
- ``insert($index, ...$values): void`` - insert one or more values at the given index
- ``pad($length, $value): void`` - pad the collection with a value to the specified length
- ``remove(...$indexes): void`` - remove values at the given indexes
- ``clear(): void`` - remove all values
- ``splice($index, $length = null, $replacement = null): void`` - remove or replace a part of the collection
- ``sum(): int|float`` - calculate the sum of all values
- ``product(): int|float`` - calculate the product of all values
- ``implode($delimiter = ''): string`` - join all values using a delimiter
- ``reduce($callback, $initial = null): mixed`` - reduce the collection to a single value
- ``reverse(): self`` - reverse the collection
- ``chunk($size): self[]`` - split the collection into chunks of the given size
- ``split($number): self[]`` - split the collection into the given number of chunks
- ``unique(): self`` - get unique values
- ``shuffle(): self`` - get values in random order
- ``random($count): self`` - get N random values from the collection
- ``column($key): self`` - gather values from a property or array index of all object or array values
- ``mapColumn($valueKey, $indexKey): Map`` - build a map using properties or array indexes of all object or array values
- ``filter($filter): self`` - filter values using the given callback
- ``apply($callback): self`` - apply the callback to all values
- ``map($mapper): Map`` - convert the collection to a map
- ``merge(...$iterables): self`` - merge the collection with the given iterables
- ``intersect(...$iterables): self`` - compute an intersection with the given iterables
- ``uintersect($comparator, ...$iterables): self`` - compute an intersection with the given iterables using a custom comparator
- ``diff(...$iterables): self`` - compute a difference between this collection and the given iterables
- ``udiff($comparator, ...$iterables): self`` - compute a difference between this collection and the given iterables using a custom comparator
- ``sort($flags = SORT_REGULAR, $reverse = false): self`` - sort the collection
- ``usort($comparator): self`` - sort the collection using a custom comparator

.. NOTE::

   Any method that returns ``self`` returns a new collection instance with the selected or modified values.
   The original collection is not changed.

   If updating the original collection is needed, use ``setValues()`` to do so, e.g.:

   .. code:: php

      <?php

      $collection->setValues($collection->sort());

Array access and iteration
==========================

``Collection`` instances can be accessed and iterated as regular arrays.

.. code:: php

   <?php

   use Kuria\Collections\Collection;

   $collection = Collection::create();

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

   $map = Map::create();


Using an existing `iterable <http://php.net/manual/en/language.types.iterable.php>`_
------------------------------------------------------------------------------------

.. code:: php

   <?php

   use Kuria\Collections\Map;

   $map = Map::create(['foo' => 'bar', 'bar' => 'baz']);


Map method overview
===================

Refer to doc comments of the respective methods for more information.

Static methods
--------------

- ``create($pairs = null): self`` - create a map from an iterable
- ``map($iterable, $mapper): self`` - map values of the given iterable using a callback
- ``build($iterable, $mapper): self`` - build a map from an iterable using a callback
- ``combine($keys, $values): self`` - combine a list of keys and a list of values to create a map


Instance methods
----------------

- ``setPairs($pairs): void`` - replace all pairs with the given iterable
- ``toArray(): array`` - get all pairs as an array
- ``isEmpty(): bool`` - see if the map is empty
- ``count(): int`` - count pairs
- ``has($key): bool`` - see if the given key exists
- ``contains($value, $strict = true): bool`` - see if the given value exists
- ``find($value, $strict = true): string|int|null`` - try to find the first occurence of a value
- ``get($key): mixed`` - get value for the given key
- ``values(): Collection`` - get all values
- ``keys(): Collection`` - get all keys
- ``set($key, $value): void`` - define a pair
- ``add(...$iterables): void`` - add pairs from other iterables to this map
- ``fill($keys, $value): void`` - fill specific keys with a value
- ``remove(...$keys): void`` - remove pairs with the given keys
- ``clear(): void`` - remove all pairs
- ``reduce($reducer, $initial = null): mixed`` - reduce the map to a single value
- ``flip(): self`` - swap keys and values
- ``shuffle(): self`` - randomize pair order
- ``column($key, $indexBy = null): self`` - gather values from properties or array keys of all object or array values
- ``filter($filter): self`` - filter pairs using the given callback
- ``apply($callback): self`` - apply the callback to all pairs
- ``map($mapper): self`` - remap pairs using the given callback
- ``intersect(...$iterables): self`` - compute an intersection with the given iterables
- ``uintersect($comparator, ...$iterables): self`` - compute an intersection with the given iterables using a custom comparator
- ``intersectKeys(...$iterables): self`` - compute a key intersection with the given iterables
- ``uintersectKeys($comparator, ...$iterables): self`` - compute a key intersection with the given iterables using a custom comparator
- ``diff(...$iterables): self`` - compute a difference between this map and the given iterables
- ``udiff($comparator, ...$iterables): self`` - compute a difference between this map and the given iterables using a custom comparator
- ``diffKeys(...$iterables): self`` - compute a key difference between this map and the given iterables
- ``udiffKeys($comparator, ...$iterables): self`` - compute a key difference between this map and the given iterables using a custom comparator
- ``sort($flags = SORT_REGULAR, $reverse = false): self`` - sort the map using its values
- ``usort($comparator): self`` - sort the map using its values and a custom comparator
- ``ksort($flags = SORT_REGULAR, $reverse = false): self`` - sort the map using its keys
- ``uksort(): self`` - sort the map using its keys and a custom comparator

.. NOTE::

   Any method that returns ``self`` returns a new map instance with the selected or modified pairs.
   The original map is not changed.

   If updating the original map is needed, use ``setPairs()`` to do so, e.g.:

   .. code:: php

      <?php

      $map->setPairs($map->sort());


Array access and iteration
==========================

``Map`` instances can be accessed and iterated as regular arrays.

.. code:: php

   <?php

   use Kuria\Collections\Map;

   $map = Map::create();

   // add some pairs
   $map['foo'] = 'bar';
   $map['baz'] = 'qux';
   $map['quux'] = 'quuz';

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
  quux: quuz
