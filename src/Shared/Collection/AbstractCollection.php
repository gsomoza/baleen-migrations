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

use ArrayIterator;
use Baleen\Migrations\Exception\InvalidArgumentException;
use Baleen\Migrations\Exception\Version\Collection\AlreadyExistsException;
use Baleen\Migrations\Exception\Version\Collection\CollectionException;
use Baleen\Migrations\Version\VersionInterface;
use Closure;
use Zend\Stdlib\ArrayUtils;

/**
 * Based on the Doctrine\CollectionAbstract project.
 *
 * @see https://github.com/doctrine/collections
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
abstract class AbstractCollection implements CollectionInterface
{
    /**
     * An array containing the entries of this collection.
     *
     * @var VersionInterface[]
     */
    private $elements;

    /**
     * Initializes a new AbstractCollection.
     *
     * @param VersionInterface[] $elements
     *
     * @throws AlreadyExistsException
     * @throws CollectionException
     * @throws InvalidArgumentException
     */
    public function __construct($elements = [])
    {
        if (!is_array($elements)) {
            if ($elements instanceof \Traversable) {
                $elements = ArrayUtils::iteratorToArray($elements);
            } else {
                throw new InvalidArgumentException(
                    "Constructor parameter 'versions' must be an array or Traversable object."
                );
            }
        }

        $this->elements = [];
        foreach ($elements as $element) {
            if (!is_object($element) || !$element instanceof VersionInterface) {
                throw CollectionException::invalidObjectException($element, VersionInterface::class);
            }
            $this->add($element);
        }
    }

    /**
     * {@inheritDoc}
     */
    final public function toArray()
    {
        return $this->elements;
    }

    /**
     * {@inheritDoc}
     */
    final public function first()
    {
        return reset($this->elements);
    }

    /**
     * {@inheritDoc}
     */
    final public function last()
    {
        return end($this->elements);
    }

    /**
     * {@inheritDoc}
     */
    final public function key()
    {
        return key($this->elements);
    }

    /**
     * {@inheritDoc}
     */
    final public function next()
    {
        return next($this->elements);
    }

    /**
     * {@inheritDoc}
     */
    final public function current()
    {
        return current($this->elements);
    }

    /**
     * {@inheritDoc}
     */
    public function remove($key)
    {
        $key = (string) $key;
        if (!isset($this->elements[$key]) && !array_key_exists($key, $this->elements)) {
            return null;
        }

        $removed = $this->elements[$key];
        unset($this->elements[$key]);

        return $removed;
    }

    /**
     * {@inheritDoc}
     */
    final public function contains($key)
    {
        $key = (string) $key;
        return isset($this->elements[$key]) || array_key_exists($key, $this->elements);
    }

    /**
     * {@inheritDoc}
     */
    final public function containsVersion(VersionInterface $version)
    {
        $key = (string) $version->getId();
        return $this->contains($key);
    }

    /**
     * {@inheritDoc}
     */
    final public function exists(Closure $p)
    {
        foreach ($this->elements as $key => $element) {
            if ($p($key, $element)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    final public function get($key)
    {
        $key = (string) $key;
        return isset($this->elements[$key]) ? $this->elements[$key] : null;
    }

    /**
     * {@inheritDoc}
     */
    final public function getKeys()
    {
        return array_keys($this->elements);
    }

    /**
     * {@inheritDoc}
     */
    final public function getValues()
    {
        return array_values($this->elements);
    }

    /**
     * {@inheritDoc}
     */
    final public function count()
    {
        return count($this->elements);
    }

    /**
     * {@inheritDoc}
     */
    public function replace(VersionInterface $version)
    {
        $key = $version->getId()->toString();
        $replacedElement = $this->contains($key) ? $this->get($key) : null;
        $this->elements[$key] = $version;

        return $replacedElement;
    }

    /**
     * {@inheritDoc}
     */
    public function add(VersionInterface $value)
    {
        $key = $value->getId()->toString();
        if ($this->contains($key)) {
            throw new AlreadyExistsException(sprintf(
                'Element with key "%s" already exists. Remove it first or use replace() if you want to overwrite it.',
                $key
            ));
        }
        $this->elements[$key] = $value;
        return true;
    }

    /**
     * {@inheritDoc}
     */
    final public function isEmpty()
    {
        return empty($this->elements);
    }

    /**
     * Required by interface IteratorAggregate.
     *
     * {@inheritDoc}
     */
    final public function getIterator()
    {
        return new ArrayIterator($this->elements);
    }

    /**
     * {@inheritDoc}
     */
    final public function map(Closure $func)
    {
        return array_map($func, $this->elements);
    }

    /**
     * {@inheritDoc}
     */
    final public function filter(Closure $p)
    {
        return new static(array_filter($this->elements, $p));
    }

    /**
     * {@inheritDoc}
     */
    final public function forAll(Closure $p)
    {
        foreach ($this->elements as $key => $element) {
            if (!$p($key, $element)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    final public function partition(Closure $p)
    {
        $matches = $noMatches = array();

        foreach ($this->elements as $key => $element) {
            if ($p($key, $element)) {
                $matches[$key] = $element;
            } else {
                $noMatches[$key] = $element;
            }
        }

        return array(new static($matches), new static($noMatches));
    }

    /**
     * Returns a string representation of this object.
     *
     * @return string
     */
    public function __toString()
    {
        return __CLASS__ . '@' . spl_object_hash($this);
    }

    /**
     * {@inheritDoc}
     */
    public function clear()
    {
        $this->elements = [];
    }

    /**
     * @inheritDoc
     */
    final public function slice($offset, $length = null)
    {
        return array_slice($this->elements, $offset, $length, true);
    }

    /**
     * @inheritdoc
     */
    final public function merge(CollectionInterface $collection)
    {
        foreach ($collection as $version) {
            $this->replace($version);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    final public function getPosition($key) {
        return array_search($key, $this->getKeys()) + 1;
    }

    /**
     * @inheritdoc
     */
    final public function getByPosition($position)
    {
        $index = $position - 1;
        $keys = $this->getKeys();
        if (!isset($keys[$index]) || !array_key_exists($index, $keys)) {
            return null;
        }
        return $this->get($keys[$index]);
    }
}
