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
    const LAST = 'last';
    const FIRST = 'first';

    /**
     * @var array
     */
    protected $aliases = [
        self::LAST  => self::LAST,
        'latest'    => self::LAST, // an alternative to 'last'
        self::FIRST => self::FIRST,
        'earliest'  => self::FIRST, // an alternative to 'first'
    ];

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
     * @return Version
     */
    public function first()
    {
        $this->rewind();
        return $this->current();
    }

    /**
     * @param mixed $index
     * @param mixed $defaultValue Will be returned if the index is not present at collection
     *
     * @return Version|null Null if not present
     */
    public function get($index, $defaultValue = null)
    {
        return $this->getByAlias($index) ?: parent::get($index, $defaultValue);
    }

    /**
     * @param string $index
     * @return Version|null
     */
    public function getByAlias($index)
    {
        $index = (string) $index;
        $version = null;
        if (isset($this->aliases[$index])) {
            $index = $this->aliases[$index];
            switch ($index) {
                case self::LAST:
                    $version = $this->last();
                    break;
                case self::FIRST:
                    $version = $this->first();
                    break;
            }
        }
        return $version;
    }
}
