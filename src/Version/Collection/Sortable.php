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

use Baleen\Migrations\Version\Collection;
use Baleen\Migrations\Version\Collection\Resolver\ResolverInterface;
use Baleen\Migrations\Version\Comparator\ComparatorInterface;
use Baleen\Migrations\Version\Comparator\DefaultComparator;
use Baleen\Migrations\Version\VersionInterface;
use Doctrine\Common\Collections\Collection as CollectionInterface;

/**
 * A collection of Versions.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class Sortable extends Collection
{
    /** @var ComparatorInterface */
    private $comparator;

    /** @var boolean */
    private $sorted = false;

    /**
     * @param VersionInterface[] $versions
     * @param ResolverInterface $resolver
     * @param ComparatorInterface $comparator
     *
     * @throws \Baleen\Migrations\Exception\InvalidArgumentException
     */
    public function __construct(
        $versions = [],
        ResolverInterface $resolver = null,
        ComparatorInterface $comparator = null
    ) {
        if (null === $comparator) {
            $comparator = new DefaultComparator();
        }
        $this->comparator = $comparator;

        parent::__construct($versions, $resolver);
    }

    /**
     * Sort the collection
     * @param ComparatorInterface $comparator
     * @return static
     */
    public function sort(ComparatorInterface $comparator = null)
    {
        if (null === $comparator) {
            $comparator = $this->comparator;
        }
        $elements = $this->toArray();
        usort($elements, $comparator);
        return new static($elements, $this->getResolver(), $comparator);
    }

    /**
     * Returns a collection with elements sorted in reverse order.
     *
     * @return static
     */
    public function getReverse()
    {
        return $this->sort($this->comparator->reverse());
    }

    /**
     * Merges another set into this one, replacing versions that exist and adding those that don't.
     *
     * @param CollectionInterface $collection
     * @return $this
     */
    public function merge(CollectionInterface $collection)
    {
        foreach ($collection as $version) {
            $this->addOrReplace($version);
        }

        return $this;
    }

    /**
     * Returns the element at the given position.
     *
     * @param $position
     *
     * @return null|VersionInterface
     */
    public function getByPosition($position)
    {
        return $this->get((int) $position + 1, false);
    }

    /**
     * @inheritdoc
     */
    public function add($element)
    {
        parent::add($element);
        $this->sorted = false;
    }

    /**
     * Returns whether the collection is sorted or not
     *
     * @return bool
     */
    public function isSorted()
    {
        return $this->sorted;
    }
}
