<?php

namespace Baleen\Migrations\Version\Collection;

use Baleen\Migrations\Exception\CollectionException;
use Baleen\Migrations\Exception\InvalidArgumentException;
use Baleen\Migrations\Version;
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
 * @method Version current()
 * @method Version[] getItems()
 * @method Version get($index, $defaultValue = null)
 */
class IndexedVersions implements CollectionDirectAccessInterface
{
    use CountableTrait, EmptyTrait, IterableTrait, GetItemsTrait, DirectAccessTrait;

    /**
     * @var array
     */
    protected $items = array();

    /**
     * @param array $versions
     *
     * @throws InvalidArgumentException
     */
    public function __construct($versions = array())
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
        foreach ($versions as $version) {
            $this->add($version);
        }
    }

    /**
     * Returns true if the specified version is valid (can be added) to the collection. Otherwise, it MUST throw
     * an exception.
     *
     * @param Version $version
     * @return bool
     * @throws InvalidArgumentException
     */
    public function isAcceptable(Version $version)
    {
        return $version; // always true
    }

    /**
     * @param mixed $version
     * @throws CollectionException
     */
    public function add($version)
    {
        if ($this->isAcceptable($version)) {
            /** @var Version $version */
            $this->items[$version->getId()] = $version;
        } else {
            // this should never happen
            throw new CollectionException(
                'For some reason isAcceptable returned a falsy value instead of throwing an exception.'
            );
        }
    }

    /**
     * @param $version
     */
    public function remove($version)
    {
        if ($this->has($version)) {
            unset($this->items[(string) $version]);
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
            $this->items[$version->getId()] = $version; // replace
        } else {
            $this->add($version);
        }
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
     * Sets the internal items pointer to the end of the array
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
