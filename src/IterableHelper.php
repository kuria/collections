<?php declare(strict_types=1);

namespace Kuria\Collections;

abstract class IterableHelper
{
    /**
     * Convert an iterable to an array with consecutive integer keys
     */
    static function iterableToList($iterable): array
    {
        return is_array($iterable) ? array_values($iterable) : iterator_to_array($iterable, false);
    }

    /**
     * Convert an iterable to an array
     */
    static function iterableToArray($iterable): array
    {
        return is_array($iterable) ? $iterable : iterator_to_array($iterable);
    }

    /**
     * Convert a list of iterables to a list of arrays
     */
    static function iterablesToArrays(...$iterables): array
    {
        $arrays = [];

        foreach ($iterables as $iterable) {
            $arrays[] = is_array($iterable) ? $iterable : iterator_to_array($iterable);
        }

        return $arrays;
    }
}
