<?php

namespace Baleen\Migrations\Version\Collection;

use Baleen\Migrations\Exception\CollectionException;
use Baleen\Migrations\Exception\InvalidArgumentException;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version\Collection\Resolver\DefaultResolverStackFactory;
use Baleen\Migrations\Version\Collection\Resolver\ResolverInterface;
use EBT\Collection\CollectionDirectAccessInterface;
use EBT\Collection\CountableTrait;
use EBT\Collection\DirectAccessTrait;
use EBT\Collection\EmptyTrait;
use EBT\Collection\GetItemsTrait;
use EBT\Collection\IterableTrait;
use Zend\Stdlib\ArrayUtils;

/**
 * Class IndexedVersions.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 *
 * IMPROVE: this class has 11 methods. Consider refactoring it to keep number of methods under 10.
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 *
 * @method Version current()
 * @method Version[] getItems()
 * @method Version baseGet($index, $defaultValue = null)
 */
class IndexedVersions implements CollectionDirectAccessInterface
{
    use CountableTrait, EmptyTrait, IterableTrait, GetItemsTrait;
    use DirectAccessTrait {
        get as _get;
        has as _has;
    }

    /**
     * @var array
     */
    protected $items = array();

    /** @var ResolverInterface */
    protected $resolver;

    /** @var string[] */
    protected $cache = [];

    /**
     * @param array $versions
     *
     * @param ResolverInterface $resolver
     * @throws CollectionException
     * @throws InvalidArgumentException
     */
    public function __construct($versions = array(), ResolverInterface $resolver = null)
    {
        if (!is_array($versions)) {
            if ($versions instanceof \Traversable) {
                $versions = ArrayUtils::iteratorToArray($versions);
            } else {
                throw new InvalidArgumentException(
                    "Constructor parameter 'versions' must be an array or traversable"
                );
            }
        }
        if (null !== $resolver) {
            $this->setResolver($resolver);
        }
        foreach ($versions as $version) {
            $this->add($version);
        }
    }

    /**
     * @param ResolverInterface $resolver
     */
    public function setResolver(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * @return ResolverInterface
     */
    public function getResolver()
    {
        if (null === $this->resolver) {
            $factory = new DefaultResolverStackFactory();
            $this->resolver = $factory->create();
        }
        return $this->resolver;
    }

    /**
     * @param mixed $index
     * @param mixed $defaultValue Will be returned if the index is not present at collection.
     * @param bool $resolve Whether to use the resolver or not.
     * @param bool $cache Whether to use the cache or not. Forced to false if $resolve = false.
     * @return Version|null Null if not present
     */
    public function get($index, $defaultValue = null, $resolve = true, $cache = true)
    {
        $index = (string)$index;

        $result = null;

        if ($resolve) {
            $result = $this->resolve($index, $cache);
        }

        if (null === $result) {
            $result = $this->_get($index, $defaultValue);
        }

        return $result;
    }

    /**
     * Resolves an alias in to a version
     * @param $alias
     * @param bool|true $cache
     * @return Version|null
     */
    protected function resolve($alias, $cache = true)
    {
        $result = null;
        if ($cache && !empty($this->cache[$alias])) {
            $result = $this->cache[$alias];
        } else {
            $result = $this->getResolver()->resolve($alias, $this);
            if ($cache) {
                $this->cache[$alias] = $result;
            }
        }
        return $result;
    }

    /**
     * Returns whether the index exists in the collection
     *
     * @param      $index
     * @param bool $resolve
     *
     * @return bool
     */
    public function has($index, $resolve = false)
    {
        $index = (string)$index;

        return $this->get($index, null, $resolve) !== null;
    }

    /**
     * Returns true if the specified version is valid (can be added) to the collection. Otherwise, it MUST throw
     * an exception.
     *
     * @param Version $version
     *
     * @return bool
     *
     * @throws CollectionException
     */
    public function validate(Version $version)
    {
        if ($this->has($version->getId())) {
            throw new CollectionException(
                sprintf('Item with id "%s" already exists', $version->getId())
            );
        }

        return true; // if there are no exceptions then result is true
    }

    /**
     * invalidate
     */
    protected function invalidate()
    {
        $this->cache = [];
    }

    /**
     * @param mixed $version
     *
     * @throws CollectionException
     */
    public function add($version)
    {
        if ($this->validate($version)) {
            /* @var Version $version */
            $this->items[$version->getId()] = $version;
            $this->invalidate();
        } else {
            // this should never happen
            throw new CollectionException(
                'Validate should either return true or throw an exception'
            );
        }
    }

    /**
     * @param $version
     */
    public function remove($version)
    {
        if ($this->has($version)) {
            unset($this->items[(string)$version]);
            $this->invalidate();
        }
    }

    /**
     * Adds a new version to the collection if it doesn't exist or replaces it if it does.
     *
     * @param Version $version
     */
    public function addOrReplace(Version $version)
    {
        if ($this->has($version)) {
            $this->remove($version);
        }
        $this->add($version);
    }

    /**
     * Sets the internal items pointer to the previous item.
     */
    public function prev()
    {
        $items = &$this->getItems();
        prev($items);
    }

    /**
     * Sets the internal items pointer to the end of the array.
     */
    public function end()
    {
        $items = &$this->getItems();
        end($items);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->getItems();
    }
}
