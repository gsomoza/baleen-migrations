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

namespace Baleen\Migrations\Version\Collection;

use Baleen\Migrations\Exception\InvalidArgumentException;
use Baleen\Migrations\Exception\Version\Collection\CollectionException;
use Baleen\Migrations\Shared\Collection\AbstractCollection;
use Baleen\Migrations\Version\Collection\Resolver\DefaultResolverStackFactory;
use Baleen\Migrations\Version\Collection\Resolver\ResolverInterface;
use Baleen\Migrations\Version\Comparator\ComparatorInterface;
use Baleen\Migrations\Version\Comparator\MigrationComparator;
use Baleen\Migrations\Version\VersionInterface;

/**
 * Class CollectionAbstract.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 *
 * IMPROVE: this class has many methods. Consider refactoring it to keep number of methods under 10.
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends AbstractCollection implements ResolvableCollectionInterface
{
    /** @var ResolverInterface */
    private $resolver;

    /** @var ComparatorInterface */
    private $comparator;

    /**
     * @param VersionInterface[]|\Traversable $versions
     * @param ResolverInterface $resolver
     * @param ComparatorInterface $comparator
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        $versions = [],
        ResolverInterface $resolver = null,
        ComparatorInterface $comparator = null
    ) {
        if (null === $resolver) {
            $resolver = DefaultResolverStackFactory::create();
        }
        $this->resolver = $resolver;

        if (null === $comparator) {
            $comparator = new MigrationComparator();
        }
        $this->comparator = $comparator;

        parent::__construct($versions);
    }

    /**
     * @return ResolverInterface
     */
    final protected function getResolver()
    {
        return $this->resolver;
    }

    /**
     * Gets an element.
     *
     * @param mixed $element If an alias is given then it will be resolved to an element. Otherwise the $key will be used
     *                   to fetch the element by index.
     * @param bool $resolve Whether to use the resolver or not.
     *
     * @return VersionInterface|null Null if not present
     */
    public function find($element, $resolve = true)
    {
        $result = null;

        if (is_object($element)) {
            $element = (string) $element;
        }

        if ($resolve && is_string($element)) {
            $result = $this->getResolver()->resolve($element, $this);
        }

        if (null === $result && is_scalar($element)) {
            $result = $this->get($element);
        }

        return $result;
    }

    /**
     * Returns whether the key exists in the collection.
     *
     * @param      $index
     * @param bool $resolve
     *
     * @return bool
     */
    public function has($index, $resolve = true)
    {
        return $this->find($index, $resolve) !== null;
    }

    /**
     * @inheritdoc
     */
    public function validate(VersionInterface $version)
    {
        return !empty($version->getId()); // basically: any VersionInterface is valid for this collection
    }

    /**
     * invalidateResolverCache
     */
    final protected function invalidateResolverCache()
    {
        $this->getResolver()->clearCache($this);
    }

    /**
     * Add a version to the collection
     *
     * @param VersionInterface $version
     *
     * @return bool
     *
     * @throws CollectionException
     * @throws InvalidArgumentException
     */
    public function add(VersionInterface $version)
    {
        $this->validate($version);
        $result = parent::add($version);
        if ($result) {
            $this->invalidateResolverCache();
        }

        return $result;
    }

    /**
     * @param $key
     *
     * @return VersionInterface|null
     */
    public function remove($key)
    {
        $result = parent::remove($key);
        if ($result) {
            $this->invalidateResolverCache();
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function replace(VersionInterface $version)
    {
        $this->validate($version);
        $removedElement = parent::replace($version);
        $this->invalidateResolverCache();

        return $removedElement;
    }

    /**
     * {@inheritDoc}
     */
    public function clear()
    {
        parent::clear();
        $this->invalidateResolverCache();
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
        uasort($elements, $comparator);
        return new static($elements, $this->getResolver(), $comparator);
    }

    /**
     * Returns a collection with elements sorted in reverse order.
     *
     * @return static
     */
    public function getReverse()
    {
        return $this->sort($this->comparator->getReverse());
    }

    /**
     * @return ComparatorInterface
     */
    public function getComparator()
    {
        return $this->comparator;
    }
}
