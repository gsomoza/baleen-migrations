<?php

namespace Baleen\Migrations\Version\Collection;

use Baleen\Migrations\Exception\MigrationException;
use Baleen\Migrations\Version;
use EBT\Collection\CollectionDirectAccessInterface;
use EBT\Collection\CountableTrait;
use EBT\Collection\EmptyTrait;
use EBT\Collection\GetItemsTrait;
use EBT\Collection\IterableTrait;
use EBT\Collection\ResourceNotFoundException;
use Zend\Stdlib\ArrayUtils;

/**
 * Class BaseCollection.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class BaseCollection implements CollectionDirectAccessInterface
{
    use CountableTrait;
    use EmptyTrait;
    use IterableTrait;
    use GetItemsTrait;

    /**
     * @var array
     */
    protected $items = array();

    /**
     * @param array $versions
     *
     * @throws MigrationException
     */
    public function __construct($versions = array())
    {
        if (!is_array($versions)) {
            if ($versions instanceof \Traversable) {
                $versions = ArrayUtils::iteratorToArray($versions);
            } else {
                throw new MigrationException(
                    "Constructor parameter 'versions' must be an array or traversable"
                );
            }
        }
        $versions = array_unique($versions, SORT_REGULAR);
        foreach ($versions as $version) {
            if (!$version instanceof Version) {
                throw new MigrationException(
                    // wait until PHP 5.5 to do Version::class
                    sprintf('Expected all versions to be of type "%s"', get_class(new Version('1')))
                );
            }
            $this->items[$version->getId()] = $version;
        }
    }

    /**
     * Return the current element.
     *
     * @return Version
     */
    public function current()
    {
        return current($this->getItems());
    }

    /**
     * Return the key of the current element.
     *
     * @return string|null string on success, or <b>null</b> on failure.
     */
    public function key()
    {
        return key($this->getItems());
    }

    /**
     * Set the internal pointer to the end of the array.
     */
    public function end()
    {
        end($this->getItems());
    }

    /**
     * Returns the previous element.
     *
     * @return mixed
     */
    public function prev()
    {
        return prev($this->getItems());
    }

    /**
     * @param mixed $idOrVersion
     * @param mixed $defaultValue Will be returned if the index is not present at collection
     *
     * @return Version|null Null if not present
     */
    public function get($idOrVersion, $defaultValue = null)
    {
        $id = $this->getVersionId($idOrVersion);

        $items = $this->getItems();

        return isset($items[$id]) ? $items[$id] : $defaultValue;
    }

    /**
     * @param mixed $index
     *
     * @return Version
     *
     * @throws ResourceNotFoundException
     */
    public function getOrException($index)
    {
        $value = $this->get($index, null);
        if ($value === null) {
            throw new ResourceNotFoundException(sprintf('Get failed, index "%s" not found.', $index));
        }

        return $value;
    }

    /**
     * @param $indexOrVersion
     *
     * @return bool True if that index or version is present
     */
    public function has($indexOrVersion)
    {
        $index = $this->getVersionId($indexOrVersion);

        return $this->get($index, null) !== null;
    }

    /**
     * @param $idOrVersion
     *
     * @return string
     *
     * @throws MigrationException
     */
    protected function getVersionId($idOrVersion)
    {
        if (is_object($idOrVersion)) {
            if ($idOrVersion instanceof Version) {
                $idOrVersion = $idOrVersion->getId();
            } else {
                throw new MigrationException(
                    sprintf('Invalid object of type "%" - expected \Baleen\Version', get_class($idOrVersion))
                );
            }
        }

        return (string) $idOrVersion;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->getItems();
    }
}
