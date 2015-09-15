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

namespace Baleen\Migrations\Version\Collection;

use Baleen\Migrations\Version;

/**
 * A collection of Versions.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class SortableVersions extends IndexedVersions
{
    /**
     * @param callable $comparator
     */
    public function sortWith(callable $comparator)
    {
        uasort($this->items, $comparator);
    }

    /**
     * @return $this The reversed collection
     */
    public function getReverse()
    {
        return new static(array_reverse($this->items));
    }

    /**
     * Merges another set into this one, replacing versions that exist and adding those that don't.
     *
     * @param SortableVersions $collection
     *
     * @return $this
     */
    public function merge(SortableVersions $collection)
    {
        foreach ($collection as $version) {
            $this->addOrReplace($version);
        }

        return $this;
    }

    /**
     * Returns the last Version in the collection.
     *
     * @return Version
     */
    public function last()
    {
        $this->end();

        return $this->current();
    }

    /**
     * Returns the first Version in the collection.
     *
     * @return Version
     */
    public function first()
    {
        $this->rewind();

        return $this->current();
    }

    /**
     * Returns the numeric position of an item in the collection (base 1).
     *
     * @param Version|string $index
     *
     * @return int
     */
    public function getPosition($index)
    {
        return array_search((string)$index, array_keys($this->items)) + 1;
    }

    /**
     * getByPosition
     * @param $position
     * @return null|Version
     */
    public function getByPosition($position)
    {
        if (empty($this->items)) {
            return null;
        }
        $result = null;
        $keys = array_keys($this->items);
        $position -= 1; // convert to base 0
        if (isset($keys[$position])) {
            $key = $keys[$position];
            $result = $this->items[$key];
        }
        return $result;
    }
}
