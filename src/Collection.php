<?php declare(strict_types=1);

namespace Kuria\Collections;

/**
 * List of values with sequential integer indexes
 */
class Collection implements \Countable, \ArrayAccess, \IteratorAggregate
{
    /** @var array */
    private $values;

    /**
     * Internal constructor
     */
    private function __construct(array $values = [])
    {
        $this->values = $values;
    }

    /**
     * Create a collection from an iterable
     *
     * If no values are given, an empty collection will be created.
     */
    static function create(?iterable $values = null): self
    {
        return new static($values ? IterableHelper::toList($values) : []);
    }

    /**
     * Create a collection from the passed arguments
     */
    static function collect(...$values): self
    {
        return new static($values);
    }

    /**
     * Create a collection and populate it with repeated values
     *
     * @return static
     */
    static function fill($value, int $count): self
    {
        if ($count <= 0) {
            return new static();
        }

        return new static(array_fill(0, $count, $value));
    }

    /**
     * Create a collection by splitting a string
     *
     * If $limit is negative, all parts except the last -$limit will be returned.
     *
     * @return static
     */
    static function explode(string $string, string $delimiter, int $limit = PHP_INT_MAX)
    {
        return new static(explode($delimiter, $string, $limit));
    }

    /**
     * Replace all values with the given iterable
     */
    function setValues(iterable $values): void
    {
        $this->values = IterableHelper::toList($values);
    }

    /**
     * Get all values as an array
     */
    function toArray(): array
    {
        return $this->values;
    }

    /**
     * See if the collection is empty
     */
    function isEmpty(): bool
    {
        return empty($this->values);
    }

    /**
     * See if the given index exists
     */
    function has(int $index): bool
    {
        return key_exists($index, $this->values);
    }

    /**
     * See if the given value exists
     */
    function contains($value, bool $strict = true): bool
    {
        return in_array($value, $this->values, $strict);
    }

    /**
     * Try to find the first occurence of a value
     *
     * Returns the found index or NULL.
     */
    function find($value, bool $strict = true): ?int
    {
        $index = array_search($value, $this->values, $strict);

        return $index !== false ? $index : null;
    }

    /**
     * Get value at the given index
     *
     * Returns NULL if the index does not exist.
     */
    function get(int $index)
    {
        return $this->values[$index] ?? null;
    }

    /**
     * Get first value
     *
     * Returns NULL if the list is empty.
     */
    function first()
    {
        return $this->values[0] ?? null;
    }

    /**
     * Get last value
     *
     * Returns NULL if the list is empty.
     */
    function last()
    {
        if (!empty($this->values)) {
            return $this->values[count($this->values) - 1];
        }

        return null;
    }

    /**
     * Get all indexes
     *
     * @return int[]
     */
    function indexes(): array
    {
        return array_keys($this->values);
    }

    /**
     * Extract a slice of the collection
     *
     * Both $index and $length can be negative, in which case they are relative to the end of the collection.
     *
     * @return static
     */
    function slice(int $index, ?int $length = null): self
    {
        return new static(array_slice($this->values, $index, $length));
    }

    /**
     * Replace a value at the given index
     *
     * @throws \OutOfBoundsException if no such index exists
     */
    function replace(int $index, $value): void
    {
        if (!key_exists($index, $this->values)) {
            throw new \OutOfBoundsException(sprintf(
                'Cannot replace value at index %d because it does not exist (%s)',
                $index,
                $this->isEmpty()
                    ? 'the collection is empty'
                    : sprintf('valid indexes are 0 to %d', $this->count() - 1)
            ));
        }

        $this->values[$index] = $value;
    }

    /**
     * Push one or more values onto the end of the collection
     */
    function push(...$values): void
    {
        if (!empty($values)) {
            array_push($this->values, ...$values);
        }
    }

    /**
     * Pop a value off the end of the collection
     *
     * Returns NULL if the collection is empty.
     */
    function pop()
    {
        return array_pop($this->values);
    }

    /**
     * Prepend one or more values to the beginning of the collection
     *
     * Multiple values are prepended as a whole, so they stay in the same order.
     */
    function unshift(...$values): void
    {
        if (!empty($values)) {
            array_unshift($this->values, ...$values);
        }
    }

    /**
     * Shift a value off the beginning of the collection
     *
     * Returns NULL if the collection is empty.
     */
    function shift()
    {
        return array_shift($this->values);
    }

    /**
     * Insert one or more values at the given index
     *
     * Any existing values at or after the index will be re-indexed.
     */
    function insert(int $index, ...$values): void
    {
        if ($values) {
            array_splice($this->values, $index, 0, $values);
        }
    }

    /**
     * Pad the collection with a value to the specified length
     *
     * If $length is positive, the new values are appended. Otherwise they are prepended.
     */
    function pad(int $length, $value): void
    {
        $this->values = array_pad($this->values, $length, $value);
    }

    /**
     * Remove values at the given indexes
     *
     * Any values after each removed index will be re-indexed.
     */
    function remove(int ...$indexes): void
    {
        if (empty($indexes) || empty($this->values)) {
            return;
        }

        if (count($indexes) === 1) {
            array_splice($this->values, $indexes[0], 1);

            return;
        }

        foreach ($indexes as $index) {
            unset($this->values[$index]);
        }

        // reindex values
        $this->values = array_values($this->values);
    }

    /**
     * Remove all values
     */
    function clear(): void
    {
        $this->values = [];
    }

    /**
     * Remove or replace a part of the collection
     *
     * Both $index and $length can be negative, in which case they are relative to the end of the collection.
     *
     * If $length is NULL, all elements until the end of the collection are removed or replaced.
     */
    function splice(int $index, ?int $length = null, ?iterable $replacement = null): void
    {
        array_splice(
            $this->values,
            $index,
            $length ?? count($this->values),
            $replacement ? IterableHelper::toArray($replacement) : null
        );
    }

    /**
     * Calculate the sum of all values (value1 + ... + valueN)
     *
     * Note that this method only operates on numeric values.
     *
     * Returns 0 if the collection is empty.
     *
     * @return int|float
     */
    function sum()
    {
        return array_sum($this->values);
    }

    /**
     * Calculate the product of all values (value1 * ... * valueN)
     *
     * Note that this method only operates on numeric values.
     *
     * Returns 1 if the collection is empty.
     *
     * @return int|float
     */
    function product()
    {
        return array_product($this->values);
    }

    /**
     * Join all values using a delimiter
     *
     * All values must be convertable to a string.
     */
    function implode(string $delimiter = ''): string
    {
        return implode($delimiter, $this->values);
    }

    /**
     * Reduce the collection to a single value
     *
     * The callback should accept 2 arguments (iteration result and current value)
     * and return a new iteration result. The returned iteration result will be
     * used in subsequent callback invocations.
     *
     * Returns the final iteration result or $initial if the collection is empty.
     *
     * Comparator signature: ($result, $value): mixed
     */
    function reduce(callable $reducer, $initial = null)
    {
        return array_reduce($this->values, $reducer, $initial);
    }

    /**
     * Reverse the collection
     *
     * Returns a new collection with values in reverse order.
     *
     * @return static
     */
    function reverse(): self
    {
        return new static(array_reverse($this->values));
    }

    /**
     * Split the collection into chunks of the given size
     *
     * The last chunk might be smaller if collection size is not a multiple of $size.
     *
     * $size must be >= 1.
     *
     * @return static[]
     */
    function chunk(int $size): array
    {
        $chunks = [];

        foreach (array_chunk($this->values, $size) as $chunk) {
            $chunks[] = new static($chunk);
        }

        return $chunks;
    }

    /**
     * Split the collection into the given number of chunks
     *
     * The last chunk might be smaller if collection size is not a multiple of $size.
     *
     * @return static[]
     */
    function split(int $number): array
    {
        if (empty($this->values) || $number < 1) {
            return [];
        }

        return $this->chunk((int) ceil($this->count() / $number));
    }

    /**
     * Get unique values
     *
     * Values are compared in non-strict mode.
     *
     * Returns a new collection with unique values.
     *
     * @return static
     */
    function unique(): self
    {
        return new static(array_values(array_unique($this->values, SORT_REGULAR)));
    }

    /**
     * Get values in random order
     *
     * Returns a new collection with values in random order.
     *
     * @return static
     */
    function shuffle(): self
    {
        $values = $this->values;
        shuffle($values);

        return new static($values);
    }

    /**
     * Get N random values from the collection
     *
     * - if $count is greater than the size of the collection, all values will be returned
     * - if $count is less than 1, an empty collection will be returned
     *
     * Returns a new collection with the randomly chosen values.
     *
     * @return static
     */
    function random(int $count): self
    {
        if ($count <= 0) {
            return new static();
        }

        if ($count >= $this->count()) {
            return $this->shuffle();
        }

        $keys = array_rand($this->values, $count);
        $values = [];

        foreach ((array) $keys as $k) {
            $values[] = $this->values[$k];
        }

        return new static($values);
    }

    /**
     * Gather values from a property or array index of all object or array values
     *
     * Returns a new collection with the gathered values.
     *
     * @return static
     */
    function column($key): self
    {
        return new static(array_column($this->values, $key));
    }

    /**
     * Build a map using properties or array indexes of all object or array values
     */
    function mapColumn($indexKey, $valueKey): Map
    {
        return Map::create(array_column($this->values, $valueKey, $indexKey));
    }

    /**
     * Filter values using the given callback
     *
     * The callback should return TRUE to accept a value and FALSE to reject it.
     *
     * Returns a new collection with all accepted values.
     *
     * Filter signature: ($value): bool
     *
     * @return static
     */
    function filter(callable $filter): self
    {
        return new static(
            $this->values
                ? array_filter($this->values, $filter)
                : []
        );
    }

    /**
     * Apply the callback to all values
     *
     * Returns a new collection with the modified values.
     *
     * Callback signature: ($value): mixed
     *
     * @return static
     */
    function apply(callable $callback): self
    {
        return new static(
            $this->values
                ? array_map($callback, $this->values)
                : []
        );
    }

    /**
     * Convert the collection to a map
     *
     * The callback should return a key for each given value.
     *
     * If the same key is returned multiple times, only the last returned value will be used.
     *
     * Mapper signature: ($value): string|int
     */
    function map(callable $mapper): Map
    {
        return Map::map($this->values, $mapper);
    }

    /**
     * Merge the collection with the given iterables
     *
     * Returns a new collection with the merged values.
     */
    function merge(iterable ...$iterables): self
    {
        $values = $this->values;

        foreach ($iterables as $iterable) {
            foreach ($iterable as $value) {
                $values[] = $value;
            }
        }

        return new static($values);
    }

    /**
     * Compute an intersection with the given iterables
     *
     * Values are converted strings before the comparison.
     *
     * Returns a new collection containing all values of this collection that are also present in all of the given iterables.
     *
     * @return static
     */
    function intersect(iterable ...$iterables): self
    {
        if (empty($this->values) || empty($iterables)) {
            return new static();
        }

        return new static(array_values(array_intersect($this->values, ...IterableHelper::toArrays($iterables))));
    }

    /**
     * Compute an intersection with the given iterables using a custom comparator
     *
     * The comparator must return an integer less than, equal to, or greater than zero if the first argument
     * is considered to be respectively less than, equal to, or greater than the second.
     *
     * Returns a new collection containing all values of this collection that are also present in all of the given iterables.
     *
     * Comparator signature: ($a, $b): int
     *
     * @return static
     */
    function uintersect(callable $comparator, iterable ...$iterables): self
    {
        if (empty($this->values) || empty($iterables)) {
            return new static();
        }

        $args = IterableHelper::toArrays($iterables);
        $args[] = $comparator;

        return new static(array_values(array_uintersect($this->values, ...$args)));
    }

    /**
     * Compute a difference between this collection and the given iterables
     *
     * Values are converted to strings before the comparison.
     *
     * Returns a new collection containing all values of this collection that are not present in any of the given iterables.
     *
     * @return static
     */
    function diff(iterable ...$iterables): self
    {
        if (empty($this->values) || empty($iterables)) {
            return new static();
        }

        return new static(array_values(array_diff($this->values, ...IterableHelper::toArrays($iterables))));
    }

    /**
     * Compute a difference between this collection and the given iterables using a custom comparator
     *
     * The comparator must return an integer less than, equal to, or greater than zero if the first argument
     * is considered to be respectively less than, equal to, or greater than the second.
     *
     * Returns a new collection containing all values of this collection that are not present in any of the given iterables.
     *
     * Comparator signature: ($a, $b): int
     *
     * @return static
     */
    function udiff(callable $comparator, iterable ...$iterables): self
    {
        if (empty($this->values) || empty($iterables)) {
            return new static();
        }

        $args = IterableHelper::toArrays($iterables);
        $args[] = $comparator;

        return new static(array_values(array_udiff($this->values, ...$args)));
    }

    /**
     * Sort the collection
     *
     * Returns a new sorted collection.
     *
     * @see SORT_REGULAR compare items normally (don't change types)
     * @see SORT_NUMERIC compare items numerically
     * @see SORT_STRING compare items as strings
     * @see SORT_LOCALE_STRING compare items as strings based on the current locale
     * @see SORT_NATURAL compare items as strings using "natural ordering" like natsort()
     * @see SORT_FLAG_CASE can be combined (bitwise OR) with SORT_STRING or SORT_NATURAL to sort strings case-insensitively
     *
     * @return static
     */
    function sort(int $flags = SORT_REGULAR, bool $reverse = false): self
    {
        if (empty($this->values)) {
            return new static();
        }

        $values = $this->values;

        if ($reverse) {
            rsort($values, $flags);
        } else {
            sort($values, $flags);
        }

        return new static($values);
    }

    /**
     * Sort the collection using a custom comparator
     *
     * The comparator should accept 2 arguments and return an integer less than, equal to, or greater than zero
     * if the first value is considered to be respectively less than, equal to, or greater than the second.
     *
     * Returns a new sorted collection.
     *
     * Comparator signature: ($a, $b): int
     *
     * @return static
     */
    function usort(callable $comparator): self
    {
        if (empty($this->values)) {
            return new static();
        }

        $values = $this->values;
        usort($values, $comparator);

        return new static($values);
    }

    function count(): int
    {
        return count($this->values);
    }

    function offsetExists($offset): bool
    {
        return key_exists($offset, $this->values);
    }

    function offsetGet($offset)
    {
        return $this->values[$offset] ?? null;
    }

    function offsetSet($offset, $value): void
    {
        if ($offset === null) {
            $this->push($value);
        } else {
            $this->replace((int) $offset, $value);
        }
    }

    function offsetUnset($offset): void
    {
        $this->remove($offset);
    }

    function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->values);
    }
}
