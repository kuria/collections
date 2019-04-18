<?php declare(strict_types=1);

namespace Kuria\Collections;

/**
 * @internal
 */
abstract class IterableHelper extends \Kuria\Iterable\IterableHelper
{
    /**
     * Convert a list of iterables to a list of arrays
     */
    static function toArrays($iterables): array
    {
        $arrays = [];

        foreach ($iterables as $iterable) {
            $arrays[] = $iterable instanceof \Traversable ? iterator_to_array($iterable) : $iterable;
        }

        return $arrays;
    }
}
