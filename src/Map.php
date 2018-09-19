<?php declare(strict_types=1);

namespace Kuria\Collections;

/**
 * List of key value pairs
 */
class Map implements \Countable, \ArrayAccess, \IteratorAggregate
{
    /** @var array */
    private $pairs;

    function __construct(?iterable $pairs = null)
    {
        if ($pairs) {
            $this->pairs = IterableHelper::toArray($pairs);
        } else {
            $this->pairs = [];
        }
    }

    /**
     * Combine a list of keys and a list of values to create a map
     *
     * Both lists must have the same number of elements. Keys must be scalar.
     *
     * @return static
     */
    static function combine(iterable $keys, iterable $values): self
    {
        return new static(array_combine(IterableHelper::toArray($keys), IterableHelper::toArray($values)));
    }

    /**
     * Get the pairs as an array
     */
    function toArray(): array
    {
        return $this->pairs;
    }

    /**
     * See if the map is empty
     */
    function isEmpty(): bool
    {
        return empty($this->pairs);
    }

    /**
     * See if the given key exists
     */
    function has($key): bool
    {
        return key_exists($key, $this->pairs);
    }

    /**
     * See if the given value exists
     */
    function contains($value, bool $strict = true): bool
    {
        return in_array($value, $this->pairs, $strict);
    }

    /**
     * Try to find the first occurence of a value
     *
     * Returns the found key or NULL.
     */
    function find($value, bool $strict = true)
    {
        $key = array_search($value, $this->pairs, $strict);

        return $key !== false ? $key : null;
    }

    /**
     * Get value for the given key
     *
     * Returns NULL if the key does not exist.
     */
    function get($key)
    {
        return $this->pairs[$key] ?? null;
    }

    /**
     * Get all values
     */
    function values(): Collection
    {
        return new Collection($this->pairs);
    }

    /**
     * Get all keys
     */
    function keys(): Collection
    {
        return new Collection(array_keys($this->pairs));
    }

    /**
     * Define a pair
     */
    function set($key, $value): void
    {
        $this->pairs[$key] = $value;
    }

    /**
     * Add pairs from other iterables to this map
     *
     * If the same key exists in multiple iterables, the last value will be used.
     */
    function add(iterable ...$others): void
    {
        foreach ($others as $other) {
            foreach ($other as $k => $v) {
                $this->pairs[$k] = $v;
            }
        }
    }

    /**
     * Fill specific keys with a value
     */
    function fill(iterable $keys, $value): void
    {
        foreach ($keys as $k) {
            $this->pairs[$k] = $value;
        }
    }

    /**
     * Remove pairs with the given keys
     */
    function remove(...$keys): void
    {
        foreach ($keys as $k) {
            unset($this->pairs[$k]);
        }
    }

    /**
     * Remove all pairs
     */
    function clear(): void
    {
        $this->pairs = [];
    }

    /**
     * Reduce the map to a single value
     *
     * The callback should accept 3 arguments (iteration result and current key and value)
     * and return a new iteration result. The returned iteration result will be
     * used in subsequent callback invocations.
     *
     * Returns the final iteration result or $initial if the map is empty.
     *
     * Callback signature: ($result, $key, $value): mixed
     */
    function reduce(callable $reducer, $initial = null)
    {
        $result = $initial;

        foreach ($this->pairs as $key => $value) {
            $result = $reducer($result, $key, $value);
        }

        return $result;
    }

    /**
     * Swap keys and values
     *
     * The values must be scalar or convertable to a string.
     *
     * @return static
     */
    function flip(): self
    {
        $pairs = [];

        foreach ($this->pairs as $k => $v) {
            $pairs[(string) $v] = $k;
        }

        return new static($pairs);
    }

    /**
     * Randomize pair order
     *
     * Returns a new map with pairs in random order.
     *
     * @return static
     */
    function shuffle(): self
    {
        $keys = array_keys($this->pairs);
        $values = $this->pairs;

        shuffle($values);

        return new static(array_combine($keys, $values));
    }

    /**
     * Gather values from properties or array keys of all object or array values
     *
     * Returns a new map with the gathered values. Preserves original keys if $indexKey is NULL.
     *
     * @return static
     */
    function column($key, $indexKey = null): self
    {
        if ($indexKey !== null) {
            return new static(array_column($this->pairs, $key, $indexKey));
        }

        // cannot use array_column() here because it does not preserve keys
        $pairs = [];

        foreach ($this->pairs as $k => $v) {
            if (is_array($v)) {
                if (key_exists($key, $v)) {
                    $pairs[$k] = $v[$key];
                }
            } elseif (is_object($v)) {
                if (isset($v->{$key}) || property_exists($v, (string) $key) && (new \ReflectionProperty($v, $key))->isPublic()) {
                    $pairs[$k] = $v->{$key};
                }
            }
        }

        return new static($pairs);
    }

    /**
     * Filter pairs using the given callback
     *
     * The callback should accept 2 arguments (key and value) return TRUE to accept a pair and FALSE to reject it.
     *
     * Returns a new map with all accepted pairs.
     *
     * Callback signature: ($key, $value): bool
     *
     * @return static
     */
    function filter(callable $filter): self
    {
        $pairs = [];

        foreach ($this->pairs as $k => $v) {
            if ($filter($k, $v)) {
                $pairs[$k] = $v;
            }
        }

        return new static($pairs);
    }

    /**
     * Remap pairs using the given callback
     *
     * The callback should accept 2 arguments (key and value) and return new key => value pairs.
     *
     * If the same key is returned multiple times, only the first returned pair with that key will be used.
     *
     * Returns a new map with the returned pairs.
     *
     * Callback signature: ($key, $value): array
     *
     * @return static
     */
    function map(callable $mapper): self
    {
        $pairs = [];

        foreach ($this->pairs as $k => $v) {
            $pairs += $mapper($k, $v);
        }

        return new static($pairs);
    }

    /**
     * Compute an intersection with the given iterables
     *
     * Values are converted to a string before the comparison.
     *
     * Returns a new map containing all pairs of this map that are also present in all of the given iterables.
     *
     * @return static
     */
    function intersect(iterable ...$others): self
    {
        if (empty($this->pairs) || empty($others)) {
            return new static();
        }

        return new static(array_intersect_assoc($this->pairs, ...IterableHelper::toArrays(...$others)));
    }

    /**
     * Compute an intersection with the given iterables using a custom comparator
     *
     * The comparator should accept 2 arguments and return an integer less than, equal to, or greater than zero
     * if the first value is considered to be respectively less than, equal to, or greater than the second.
     *
     * Returns a new map containing all pairs of this map that are also present in all of the given iterables.
     *
     * Callback signature: ($a, $b): int
     *
     * @return static
     */
    function uintersect(callable $comparator, iterable ...$others): self
    {
        if (empty($this->pairs) || empty($others)) {
            return new static();
        }

        $args = IterableHelper::toArrays(...$others);
        $args[] = $comparator;

        return new static(array_uintersect_assoc($this->pairs, ...$args));
    }

    /**
     * Compute a difference between this map and the given iterables
     *
     * Values are converted to a string before the comparison.
     *
     * Returns a new map containing all pairs of this map that are not present in any of the given iterables.
     *
     * @return static
     */
    function diff(iterable ...$others): self
    {
        if (empty($this->pairs) || empty($others)) {
            return new static();
        }

        return new static(array_diff_assoc($this->pairs, ...IterableHelper::toArrays(...$others)));
    }

    /**
     * Compute a difference between this map and the given iterables using a custom comparator
     *
     * The comparator should accept 2 arguments and return an integer less than, equal to, or greater than zero
     * if the first value is considered to be respectively less than, equal to, or greater than the second.
     *
     * Returns a new map containing all pairs of this map that are not present in any of the given iterables.
     *
     * Callback signature: ($a, $b): int
     *
     * @return static
     */
    function udiff(callable $comparator, iterable ...$others): self
    {
        if (empty($this->pairs) || empty($others)) {
            return new static();
        }

        $args = IterableHelper::toArrays(...$others);
        $args[] = $comparator;

        return new static(array_udiff_assoc($this->pairs, ...$args));
    }

    /**
     * Sort the map using its values
     *
     * Returns a new sorted map.
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
        if (empty($this->pairs)) {
            return new static();
        }

        $pairs = $this->pairs;

        if ($reverse) {
            arsort($pairs, $flags);
        } else {
            asort($pairs, $flags);
        }

        return new static($pairs);
    }

    /**
     * Sort the map using its values and a custom comparator
     *
     * The comparator should accept 2 arguments and return an integer less than, equal to, or greater than zero
     * if the first value is considered to be respectively less than, equal to, or greater than the second.
     *
     * Returns a new sorted map.
     *
     * Callback signature: ($a, $b): int
     *
     * @return static
     */
    function usort(callable $comparator): self
    {
        if (empty($this->pairs)) {
            return new static();
        }

        $pairs = $this->pairs;
        uasort($pairs, $comparator);

        return new static($pairs);
    }

    /**
     * Sort the map using its keys
     *
     * Returns a new sorted map.
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
    function ksort(int $flags = SORT_REGULAR, bool $reverse = false): self
    {
        if (empty($this->pairs)) {
            return new static();
        }

        $pairs = $this->pairs;

        if ($reverse) {
            krsort($pairs, $flags);
        } else {
            ksort($pairs, $flags);
        }

        return new static($pairs);
    }

    /**
     * Sort the map using its keys and a custom comparator
     *
     * The comparator should accept 2 arguments and return an integer less than, equal to, or greater than zero
     * if the first value is considered to be respectively less than, equal to, or greater than the second.
     *
     * Returns a new sorted map.
     *
     * Callback signature: ($a, $b): int
     *
     * @return static
     */
    function uksort(callable $comparator): self
    {
        if (empty($this->pairs)) {
            return new static();
        }

        $pairs = $this->pairs;
        uksort($pairs, $comparator);

        return new static($pairs);
    }

    function count(): int
    {
        return count($this->pairs);
    }

    function offsetExists($offset): bool
    {
        return key_exists($offset, $this->pairs);
    }

    function offsetGet($offset)
    {
        return $this->pairs[$offset] ?? null;
    }

    function offsetSet($offset, $value): void
    {
        $this->pairs[$offset] = $value;
    }

    function offsetUnset($offset): void
    {
        unset($this->pairs[$offset]);
    }

    function getIterator(): \Traversable
    {
        yield from $this->pairs;
    }
}
