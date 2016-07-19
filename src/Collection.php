<?php

namespace SevenShores\Haversack;

use ArrayAccess;
use IteratorAggregate;
use Traversable;

class Collection implements ArrayAccess, IteratorAggregate
{
    /**
     * @var array
     */
    protected $items;

    /**
     * Collection constructor.
     *
     * @param array $items
     */
    function __construct($items = [])
    {
        $this->items = $items;
    }

    /**
     * @param array $items
     * @return static
     */
    static function make($items = [])
    {
        return new static($items);
    }

    /**
     * @return array
     */
    function all()
    {
        return $this->items;
    }

    /**
     * @param int $size
     * @param bool $preserve_keys
     * @return static
     */
    function chunk($size, $preserve_keys = false)
    {
        return new static(array_chunk($this->items, $size, $preserve_keys));
    }

    /**
     * @param mixed $value
     * @return bool
     */
    function contains($value)
    {
        return in_array($value, $this->items);
    }

    /**
     * @return int
     */
    function count()
    {
        return count($this->items);
    }

    /**
     * @param array ...$arrays
     * @return static
     */
    function diff(...$arrays)
    {
        return new static(array_diff($this->items, ...$arrays));
    }

    /**
     * @param array ...$arrays
     * @return static
     */
    function diffKeys(...$arrays)
    {
        return new static(array_diff_key($this->items, ...$arrays));
    }

    /**
     * @param callable $callback
     * @return $this
     */
    function each($callback)
    {
        $this->items = $this->map($callback);
        return $this;
    }

    /**
     * @param callable $callback
     * @param int $flag
     * @return static
     */
    function filter($callback, $flag = ARRAY_FILTER_USE_BOTH)
    {
        return new static(array_filter($this->items, $callback, $flag));
    }

    /**
     * @param callable $callback
     * @return mixed
     */
    function first($callback = null)
    {
        if (! $callback) {
            return $this->items[0];
        }
        return $this->filter($callback)->first(); // TODO: Optimize
    }

    /**
     * Retrieve an external iterator
     *
     * @return Traversable
     */
    function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * @param string|int $key
     * @return bool
     */
    function has($key)
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * @return bool
     */
    function isEmpty()
    {
        return empty($this->items);
    }

    /**
     * @param callable $callback
     * @return mixed
     */
    function last($callback = null)
    {
        if (! $callback) {
            return $this->items[-1];
        }
        return $this->filter($callback)->last(); // TODO: Optimize
    }

    /**
     * @param callable $callback
     * @return static
     */
    function map($callback)
    {
        return new static(array_map($callback, $this->items, array_keys($this->items)));
    }

    /**
     * @return mixed
     */
    function max()
    {
        return max($this->items);
    }

    /**
     * @param array ...$arrays
     * @return static
     */
    function merge(...$arrays)
    {
        return new static(array_merge($this->items, ...$arrays));
    }

    /**
     * @return mixed
     */
    function min()
    {
        return min($this->items);
    }

    /**
     * Whether a offset exists
     *
     * @param mixed $offset
     * @return boolean
     */
    function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    /**
     * Offset to retrieve
     *
     * @param mixed $offset
     * @return mixed
     */
    function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    /**
     * Offset to set
     *
     * @param mixed $offset
     * @param mixed $value
     */
    function offsetSet($offset, $value)
    {
        $this->items[$offset] = $value;
    }

    /**
     * Offset to unset
     *
     * @param mixed $offset
     */
    function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    /**
     * @param callable $callback
     * @param mixed $initial
     * @return static
     */
    function reduce($callback, $initial = null)
    {
        return new static(array_reduce($this->items, $callback, $initial));
    }

    /**
     * @return static
     */
    function reverse()
    {
        return new static(array_reverse($this->items));
    }

    /**
     * @param int $offset
     * @param int|null $length
     * @param bool $preserve_keys
     * @return static
     */
    function slice($offset, $length = null, $preserve_keys = false)
    {
        return new static(array_slice($this->items, $offset, $length, $preserve_keys));
    }

    /**
     * @param int $direction
     * @return static
     */
    function sort($direction = SORT_ASC)
    {
        return $direction === SORT_DESC ? $this->sortDesc() : $this->sortAsc();
    }

    /**
     * @return static
     */
    function sortAsc()
    {
        $items = $this->items;
        asort($items);
        return new static($items);
    }

    /**
     * @return static
     */
    function sortDesc()
    {
        $items = $this->items;
        arsort($items);
        return new static($items);
    }

    /**
     * @param string $property
     * @param int $direction
     * @return static
     */
    function sortBy($property, $direction = SORT_ASC)
    {
        $items = $this->items;
        usort($items, function ($a, $b) use ($property, $direction) {
            return $direction === SORT_DESC
                ? $b->{$property} <=> $a->{$property}
                : $a->{$property} <=> $b->{$property};
        });
        return new static($items);
    }

    /**
     * @return number
     */
    function sum()
    {
        return array_sum($this->items);
    }

    /**
     * @return static
     */
    function unique()
    {
        return new static(array_unique($this->items));
    }

    /**
     * @param callable $callback
     * @return static
     */
    function userSort($callback)
    {
        $items = $this->items;
        usort($items, $callback);
        return new static($items);
    }

    /**
     * @return array
     */
    function values()
    {
        return array_values($this->items);
    }
}
