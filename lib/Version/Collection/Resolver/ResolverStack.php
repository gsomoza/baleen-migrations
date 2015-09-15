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

use Baleen\Migrations\Exception\ResolverException;
use Baleen\Migrations\Version\Collection\IndexedVersions;

/**
 * Class ResolverStack
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class ResolverStack extends AbstractResolver
{
    /** @var ResolverInterface[] */
    protected $resolvers = [];

    /**
     * addResolver
     *
     * @param ResolverInterface $resolver
     * @param int               $priority Highest priority gets executed first.
     */
    public function addResolver(ResolverInterface $resolver, $priority)
    {
        $priority = (int) $priority;
        $this->resolvers[$priority] = $resolver;
    }

    /**
     * Resolves an alias
     *
     * @param string $alias
     * @param IndexedVersions $collection
     *
     * @return \Baleen\Migrations\Version|null
     * @throws ResolverException
     */
    public function doResolve($alias, IndexedVersions $collection)
    {
        krsort($this->resolvers);
        foreach ($this->resolvers as $resolver) {
            $result = $resolver->resolve($alias, $collection);
            if ($result !== null) {
                return $result;
            }
        }
        return null;
    }
}
