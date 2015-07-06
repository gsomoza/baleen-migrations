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
 * <https://github.com/baleen/migrations>.
 */

namespace Baleen\Version;
use Baleen\Version;
use EBT\Collection\CollectionDirectAccessInterface;
use EBT\Collection\CountableTrait;
use EBT\Collection\EmptyTrait;
use EBT\Collection\GetItemsTrait;
use EBT\Collection\IterableTrait;
use EBT\Collection\ResourceNotFoundException;
use Traversable;
use Zend\Stdlib\ArrayUtils;
use Baleen\Exception\MigrationException;

/**
 * A collection of Versions
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class Collection implements CollectionDirectAccessInterface
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
        foreach($versions as $version) {
            if (!$version instanceof Version) {
                throw new MigrationException(
                    sprintf('Expected all versions to be of type "%s"', get_class(new Version('1'))) // wait until PHP 5.5 to do Version::class
                );
            }
            $this->items[$version->getId()] = $version;
        }
    }

    /**
     * @param Version $version
     * @param bool $overwrite
     * @throws MigrationException
     */
    public function add(Version $version, $overwrite = false)
    {
        if (!$overwrite && $this->has($version->getId())) {
            throw new MigrationException(
                sprintf('Item with id "%s" already exists', $version->getId())
            );
        }
        $this->items[$version->getId()] = $version;
    }

    /**
     * @param $version
     * @throws MigrationException
     */
    public function remove($version)
    {
        $version = $this->getVersionId($version);
        if ($this->has($version)) {
            unset($this->items[$version]);
        }
    }

    /**
     * @param $idOrVersion
     * @return string
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
     * @return bool True if that index or version is present
     */
    public function has($indexOrVersion)
    {
        $index = $this->getVersionId($indexOrVersion);

        return $this->get($index, null) !== null;
    }

    /**
     * @param callable $comparator
     */
    public function sortWith(callable $comparator)
    {
        uasort($this->items, $comparator);
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
     * Set the internal pointer to the end of the array
     */
    public function end()
    {
        end($this->getItems());
    }

    /**
     * Returns the previous element
     *
     * @return mixed
     */
    public function prev()
    {
        return prev($this->getItems());
    }

    public function getReverse()
    {
        return new static(array_reverse($this->items));
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->items;
    }
}
