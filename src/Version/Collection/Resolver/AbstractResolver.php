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

namespace Baleen\Migrations\Version\Collection\Resolver;

use Baleen\Migrations\Exception\Version\Collection\ResolverException;
use Baleen\Migrations\Version\Collection\Collection;
use Baleen\Migrations\Version\VersionInterface;

/**
 * Class AbstractResolver
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
abstract class AbstractResolver implements ResolverInterface
{
    /** @var array */
    private $cache = [];

    /** @var bool */
    private $cacheEnabled = true;

    /**
     * @param bool $cacheEnabled
     */
    public function __construct($cacheEnabled = true)
    {
        $this->cacheEnabled = (bool) $cacheEnabled;
    }

    /**
     * Resolves an alias into a Version.
     *
     * @param string $alias
     * @param Collection $collection
     *
     * @return VersionInterface|null
     *
     * @throws ResolverException
     */
    final public function resolve($alias, Collection $collection)
    {
        $alias = (string) $alias;

        $result = $this->cacheGet($alias, $collection);

        if (false === $result) {
            $result = $this->doResolve($alias, $collection);
            if (null !== $result && !$result instanceof VersionInterface) {
                throw new ResolverException('Expected result to be either a VersionInterface object or null.');
            }
            $this->cacheSet($alias, $collection, $result);
        }

        return $result;
    }

    /**
     * Gets an alias from the cache. Returns false if nothing could be found, a Version if the alias was previously
     * resolved to a version, and null if the alias couldn't be resolved in a previous call.
     *
     * @param string $alias
     * @param Collection $collection
     *
     * @return bool|null|VersionInterface
     */
    private function cacheGet($alias, Collection $collection)
    {
        $result = false;

        if ($this->cacheEnabled) {
            $hash = spl_object_hash($collection);
            if (isset($this->cache[$hash]) && array_key_exists($alias, $this->cache[$hash])) {
                $result = $this->cache[$hash][$alias];
            }
        }

        return $result;
    }

    /**
     * Saves the result of resolving an alias against a given collection into the cache.
     *
     * @param string $alias
     * @param \Baleen\Migrations\Version\Collection\Collection $collection
     * @param null|VersionInterface $result
     *
     * @return void
     */
    private function cacheSet($alias, $collection, $result)
    {
        if (!$this->cacheEnabled) {
            return null;
        }

        $hash = spl_object_hash($collection);
        if (!isset($this->cache[$hash])) {
            $this->cache[$hash] = []; // initialize the collection's cache
        }
        $this->cache[$hash][$alias] = $result;
    }

    /**
     * @inheritdoc
     */
    final public function clearCache(Collection $collection = null)
    {
        if (null !== $collection) {
            $hash = spl_object_hash($collection);
            unset($this->cache[$hash]);
        } else {
            $this->cache = [];
        }
    }

    /**
     * doResolve
     *
     * @param string $alias
     * @param Collection $collection
     *
     * @return VersionInterface|null
     */
    abstract protected function doResolve($alias, Collection $collection);
}
