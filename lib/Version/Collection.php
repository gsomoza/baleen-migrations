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

namespace Baleen\Migrations\Version;

use Baleen\Migrations\Version;
use Baleen\Migrations\Version\Collection\BaseCollection;
use Baleen\Migrations\Exception\MigrationException;

/**
 * A collection of Versions.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class Collection extends BaseCollection
{
    /**
     * @param Version $version
     * @param bool    $overwrite
     *
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
     *
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
     * Adds a new version to the collection if it doesn't exist, or it updates the existing version if it does.
     *
     * @param Version $version
     */
    public function addOrUpdate(Version $version)
    {
        if ($this->has($version)) {
            $this->items[$version->getId()] = $version; // replace
        } else {
            $this->add($version);
        }
    }

    /**
     * Merges another collection into this collection, replacing versions that exist and adding those that don't.
     *
     * @param Collection $collection
     *
     * @return $this
     */
    public function merge(Collection $collection)
    {
        foreach ($collection as $version) {
            $this->addOrUpdate($version);
        }

        return $this;
    }

    /**
     * @param callable $comparator
     */
    public function sortWith(callable $comparator)
    {
        uasort($this->items, $comparator);
    }

    /**
     * @return static
     */
    public function getReverse()
    {
        return new static(array_reverse($this->items));
    }
}
