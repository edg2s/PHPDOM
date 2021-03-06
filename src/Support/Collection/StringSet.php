<?php
declare(strict_types=1);

namespace Rowbot\DOM\Support\Collection;

use ArrayAccess;
use Countable;
use Iterator;
use Rowbot\DOM\Utils;

use const PREG_SPLIT_NO_EMPTY;

use function array_pop;
use function array_search;
use function array_shift;
use function array_splice;
use function array_unshift;
use function implode;
use function preg_split;

/**
 * StringSet is a collection of strings that does not allow for duplicate items.
 */
final class StringSet implements ArrayAccess, Countable, Iterator
{
    /**
     * @var array<string>
     */
    private $list;

    /**
     * @var array<string, bool>
     */
    private $cache;

    /**
     * @var int
     */
    private $length;

    /**
     * @var int
     */
    private $cursor;

    /**
     * Constructor.
     *
     * @return void
     */
    private function __construct()
    {
        $this->list = [];
        $this->cache = [];
        $this->length = 0;
        $this->cursor = 0;
    }

    /**
     * Creates a set from a string of tokens.
     *
     * @param string $input
     *
     * @return self
     */
    public static function createFromString(string $input): self
    {
        $inputTokens = preg_split(
            Utils::ASCII_WHITESPACE,
            $input,
            -1,
            PREG_SPLIT_NO_EMPTY
        );
        $tokens = new self();

        foreach ($inputTokens as $token) {
            $tokens->append($token);
        }

        return $tokens;
    }

    /**
     * Appends an item to the set if it doesn't already exsist in the set.
     *
     * @param string $item
     *
     * @return void
     */
    public function append(string $item): void
    {
        if (isset($this->cache[$item])) {
            return;
        }

        $this->list[] = $item;
        $this->cache[$item] = true;
        ++$this->length;
    }

    /**
     * Prepends an item to the set if it doesn't already exist in the set.
     *
     * @param string $item
     *
     * @return void
     */
    public function prepend(string $item): void
    {
        if (isset($this->cache[$item])) {
            return;
        }

        array_unshift($this->list, $item);
        $this->cache[$item] = true;
        ++$this->length;
    }

    /**
     * Replaces the first occurance of either item or the replacement item in
     * the set if they exist and removes all other occurances.
     *
     * @param string $item
     * @param string $newItem
     *
     * @return void
     */
    public function replace(string $item, string $newItem): void
    {
        // If for whatever reason item is being replaced by itself, just don't
        // bother doing any work.
        if ($item === $newItem) {
            return;
        }

        $containsItem = isset($this->cache[$item]);
        $containsNewItem = isset($this->cache[$newItem]);

        // If it doesn't contain item, then there is nothing to replace or move.
        if (!$containsItem) {
            return;
        }

        // We now know that the list contains item and it will ultimately be
        // removed, so remove it from the cache.
        unset($this->cache[$item]);

        if ($containsNewItem) {
            // At this point, we know that the list contains both item and
            // replacement item, so we know that item is going to be removed;
            // decrement the length.
            --$this->length;

            // If item is the last item in the list just pop it off as we don't
            // need to do anything with replacement item since it's already in
            // the list and came before item.
            if ($this->list[$this->length] === $item) {
                array_pop($this->list);
                return;
            }

            $popped = null;

            // If the replacement item is the last item in the list, pop it off
            // since we will be replacing item with replacement item later.
            if ($this->list[$this->length] === $newItem) {
                $popped = array_pop($this->list);
            }

            // Find item's position in the list.
            $itemIndex = array_search($item, $this->list, true);

            // If the replacement item wasn't the last item in the list, we need
            // to find its position in the list.
            if ($popped === null) {
                $newItemIndex = array_search($newItem, $this->list, true);

                // If item comes after the replacement item in the list, we only
                // need to remove item here since the replacement item is the
                // first instance of the two in this case.
                if ($itemIndex > $newItemIndex) {
                    array_splice($this->list, $itemIndex, 1);
                    return;
                }

                // At this point, we know that the replacement item comes after
                // item in the list. Remove it from the list so that we can
                // insert it into item's place next.
                array_splice($this->list, $newItemIndex, 1);
            }

            // We removed the existing instance of replacement item from the
            // list above and now we will replace item with replacement item.
            $this->list[$itemIndex] = $newItem;
            return;
        }

        // At this point the list only contains item and not the replacement
        // item. Add the replacement item to the cache as it is not currently in
        // the list.
        $this->cache[$newItem] = true;

        // If item is the last item in the list, pop it off the list and append
        // the replacement item.
        if ($this->list[$this->length - 1] === $item) {
            array_pop($this->list);
            $this->list[] = $newItem;
            return;
        }

        $index = array_search($item, $this->list, true);
        $this->list[$index] = $newItem;
    }

    /**
     * Inserts an item before another item in the set.
     *
     * @param string $item
     * @param string $newItem
     *
     * @return void
     */
    public function insertBefore(string $item, string $newItem): void
    {
        if (!isset($this->cache[$item])) {
            return;
        }

        if (isset($this->cache[$newItem])) {
            return;
        }

        $this->cache[$newItem] = true;
        ++$this->length;

        // If we are trying to insert before the first item in the array use
        // array_unshift instead of searching the array and then splicing the
        // value in as unshifting is faster.
        if ($this->list[0] === $item) {
            array_unshift($this->list, $newItem);
            return;
        }

        $index = array_search($item, $this->list, true);
        array_splice($this->list, $index, 0, [$newItem]);
    }

    /**
     * Removes the given item from the set.
     *
     * @param string $item
     *
     * @return void
     */
    public function remove(string $item): void
    {
        if (!isset($this->cache[$item])) {
            return;
        }

        // Remove the item from the cache.
        unset($this->cache[$item]);

        // If the given item is the last item in the array simply pop it off,
        // rather than searching the array and splicing the value out.
        if ($this->list[--$this->length] === $item) {
            array_pop($this->list);
            return;
        }

        // If item is the first item in the list shift it off the array as it
        // is faster than searching the array and then splicing out the value.
        if ($this->list[0] === $item) {
            array_shift($this->list);
            return;
        }

        $index = array_search($item, $this->list, true);
        array_splice($this->list, $index, 1);
    }

    /**
     * Empties the set.
     *
     * @return void
     */
    public function clear(): void
    {
        $this->list = [];
        $this->cache = [];
        $this->length = 0;
    }

    /**
     * Determines if the set is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->length === 0;
    }

    /**
     * Determines if the set contains the given item.
     *
     * @param string $item
     *
     * @return bool
     */
    public function contains(string $item): bool
    {
        return isset($this->cache[$item]);
    }

    /**
     * Returns the number of items in the set.
     *
     * @return int
     */
    public function count(): int
    {
        return $this->length;
    }

    /**
     * Returns a string representation of the object.
     *
     * @see https://dom.spec.whatwg.org/#ref-for-concept-ordered-set-serializer
     *
     * @return string
     */
    public function toString()
    {
        return implode("\x20", $this->list);
    }

    /**
     * Returns the entire set as an array.
     *
     * @return array<string>
     */
    public function all(): array
    {
        return $this->list;
    }

    /**
     * @param int $offset
     *
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->list[$offset]);
    }

    /**
     * @param int $offset
     *
     * @return string|null
     */
    public function offsetGet($offset): ?string
    {
        return $this->list[$offset] ?? null;
    }

    /**
     * Noop.
     *
     * @param int              $offset
     * @param string $value
     *
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
    }

    /**
     * Noop.
     *
     * @param int $offset
     *
     * @return void
     */
    public function offsetUnset($offset): void
    {
    }

    /**
     * @return string
     */
    public function current(): string
    {
        return $this->list[$this->cursor];
    }

    /**
     * @return int
     */
    public function key(): int
    {
        return $this->cursor;
    }

    /**
     * @return void
     */
    public function next(): void
    {
        ++$this->cursor;
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        $this->cursor = 0;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->list[$this->cursor]);
    }
}
