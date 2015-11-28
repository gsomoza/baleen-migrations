<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Baleen\Migrations\Shared\Collection;

use Baleen\Migrations\Version\Comparator\ComparatorInterface;
use Baleen\Migrations\Version\VersionInterface;
use Closure;
use Countable;
use IteratorAggregate;

/**
 * Interface CollectionInterface.
 *
 * Based on the Doctrine\CollectionAbstract project.
 *
 * @see https://github.com/doctrine/collections
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
interface CollectionInterface extends Countable, IteratorAggregate
{
    /**
     * Adds an element at the end of the collection.
     *
     * @param VersionInterface $element The element to add.
     *
     * @return boolean Always TRUE.
     */
    public function add(VersionInterface $element);

    /**
     * Clears the collection, removing all elements.
     *
     * @return void
     */
    public function clear();

    /**
     * Checks whether the collection contains an element with the specified key/index.
     *
     * @param string|integer $key The key/index to check for.
     *
     * @return bool TRUE if the collection contains an element with the specified key/index,
     *              FALSE otherwise.
     */
    public function contains($key);

    /**
     * Checks whether the collection contains a version with the same id as the one specified
     *
     * @param VersionInterface $version
     *
     * @return bool
     */
    public function containsVersion(VersionInterface $version);

    /**
     * Checks whether the collection is empty (contains no elements).
     *
     * @return boolean TRUE if the collection is empty, FALSE otherwise.
     */
    public function isEmpty();

    /**
     * Removes the element at the specified index from the collection.
     *
     * @param string|integer $key The kex/index of the element to remove.
     *
     * @return null|VersionInterface The removed element or NULL, if the collection did not contain the element.
     */
    public function remove($key);

    /**
     * Gets the element at the specified key/index.
     *
     * @param string|integer $key The key/index of the element to retrieve.
     *
     * @return VersionInterface|null
     */
    public function get($key);

    /**
     * Gets all keys/indices of the collection.
     *
     * @return array The keys/indices of the collection, in the order of the corresponding
     *               elements in the collection.
     */
    public function getKeys();

    /**
     * Gets all values of the collection.
     *
     * @return VersionInterface[] The values of all elements in the collection, in the order they appear in the
     *                            collection.
     */
    public function getValues();

    /**
     * Gets a native PHP array representation of the collection.
     *
     * @return VersionInterface[]
     */
    public function toArray();

    /**
     * Sets the internal iterator to the first element in the collection and returns this element.
     *
     * @return VersionInterface
     */
    public function first();

    /**
     * Sets the internal iterator to the last element in the collection and returns this element.
     *
     * @return VersionInterface
     */
    public function last();

    /**
     * Gets the key/index of the element at the current iterator getPosition.
     *
     * @return int|string
     */
    public function key();

    /**
     * Gets the element of the collection at the current iterator getPosition.
     *
     * @return VersionInterface
     */
    public function current();

    /**
     * Moves the internal iterator getPosition to the next element and returns this element.
     *
     * @return VersionInterface
     */
    public function next();

    /**
     * Tests for the existence of an element that satisfies the given predicate.
     *
     * @param Closure $p The predicate.
     *
     * @return boolean TRUE if the predicate is TRUE for at least one element, FALSE otherwise.
     */
    public function exists(Closure $p);

    /**
     * Returns all the elements of this collection that satisfy the predicate p.
     * The order of the elements is preserved.
     *
     * @param Closure $p The predicate used for filtering.
     *
     * @return CollectionInterface A collection with the results of the filter operation.
     */
    public function filter(Closure $p);

    /**
     * Tests whether the given predicate p holds for all elements of this collection.
     *
     * @param Closure $p The predicate.
     *
     * @return boolean TRUE, if the predicate yields TRUE for all elements, FALSE otherwise.
     */
    public function forAll(Closure $p);

    /**
     * Applies the given function to each element in the collection and returns
     * a new collection with the elements returned by the function.
     *
     * @param Closure $func
     *
     * @return array
     */
    public function map(Closure $func);

    /**
     * Partitions this collection in two collections according to a predicate.
     * Keys are preserved in the resulting collections.
     *
     * @param Closure $p The predicate on which to partition.
     *
     * @return CollectionInterface[] An array with two elements. The first element contains the collection
     *                               of elements where the predicate returned TRUE, the second element
     *                               contains the collection of elements where the predicate returned FALSE.
     */
    public function partition(Closure $p);

    /**
     * Extracts a slice of $length elements starting at getPosition $offset from the CollectionAbstract.
     *
     * If $length is null it returns all elements from $offset to the end of the CollectionAbstract.
     * Keys have to be preserved by this method. Calling this method will only return the
     * selected slice and NOT change the elements contained in the collection slice is called on.
     *
     * @param int      $offset The offset to start from.
     * @param int|null $length The maximum number of elements to return, or null for no limit.
     *
     * @return VersionInterface[]
     */
    public function slice($offset, $length = null);

    /**
     * Replaces a version in-place with the specified version.
     *
     * @param VersionInterface $version
     *
     * @return VersionInterface|null The replaced element or NULL, if the collection didn't contain the element.
     */
    public function replace(VersionInterface $version);

    /**
     * Returns the ordinal getPosition of the item in the array.
     *
     * @param string $key
     *
     * @return int
     */
    public function getPosition($key);

    /**
     * Returns the element at the given ordinal getPosition.
     *
     * @param int $position
     *
     * @return null|VersionInterface
     */
    public function getByPosition($position);

    /**
     * Sort the collection
     *
     * @param ComparatorInterface $comparator
     *
     * @return CollectionInterface
     */
    public function sort(ComparatorInterface $comparator = null);

    /**
     * Returns a collection with elements sorted in reverse order.
     *
     * @return CollectionInterface
     */
    public function getReverse();

    /**
     * @return ComparatorInterface
     */
    public function getComparator();

    /**
     * Merges another set into this one, replacing versions that exist and adding those that don't.
     *
     * @param CollectionInterface $collection
     *
     * @return static
     */
    public function merge(CollectionInterface $collection);

    /**
     * Returns true if the specified version is valid (can be added) to the collection. Otherwise, it MUST throw
     * an exception.
     *
     * @param VersionInterface $version
     *
     * @return bool
     */
    public function validate(VersionInterface $version);
}
