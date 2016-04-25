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

namespace Baleen\Migrations\Delta\Collection\Resolver;

use Baleen\Migrations\Exception\InvalidArgumentException;
use Baleen\Migrations\Delta\Collection\Collection;

/**
 * Class ResolverStack
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
final class ResolverStack extends AbstractResolver
{
    /** @var ResolverInterface[] */
    protected $resolvers = [];

    /**
     * ResolverStack constructor.
     *
     * @param ResolverInterface[] $resolvers
     *
     * @param bool $cacheEnabled
     * @throws InvalidArgumentException
     */
    public function __construct(array $resolvers, $cacheEnabled = true)
    {
        foreach ($resolvers as $resolver) {
            if (!is_object($resolver) || !$resolver instanceof ResolverInterface) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid resolver of type "%s". Expected instance of "%s".',
                    is_object($resolver) ? get_class($resolver) : gettype($resolver),
                    ResolverInterface::class
                ));
            }
        }
        $this->resolvers = $resolvers;
        parent::__construct($cacheEnabled);
    }

    /**
     * Resolves an alias
     *
     * @param string $alias
     * @param Collection $collection
     *
     * @return \Baleen\Migrations\Delta\DeltaInterface|null
     * @throws \Baleen\Migrations\Exception\Version\Collection\ResolverException
     */
    public function doResolve($alias, Collection $collection)
    {
        foreach ($this->resolvers as $resolver) {
            $result = $resolver->resolve($alias, $collection);
            if ($result !== null) {
                return $result;
            }
        }
        return null;
    }
}
