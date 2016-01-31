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

namespace Baleen\Migrations\Migration\Repository;

use Baleen\Migrations\Exception\InvalidArgumentException;
use Baleen\Migrations\Migration\Factory\FactoryInterface;
use Baleen\Migrations\Delta\Collection\Collection;

/**
 * A generic repository that can aggregate one or more other repositories
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
final class AggregateMigrationRepository implements MigrationRepositoryInterface
{
    /** @var \SplStack */
    private $stack;

    /**
     * AggregateMigrationRepository constructor.
     */
    public function __construct()
    {
        $this->stack = new \SplStack();
    }

    /**
     * Adds a single repository to the stack
     *
     * @param MigrationRepositoryInterface $repo
     */
    public function addRepository(MigrationRepositoryInterface $repo)
    {
        $this->stack[] = $repo;
    }

    /**
     * Adds a set of repositories to the stack
     *
     * @param $repositories
     * @throws InvalidArgumentException
     */
    public function addRepositories($repositories)
    {
        if (!is_array($repositories) && (!is_object($repositories) || !$repositories instanceof \Traversable)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid argument provided for $repositories, expecting either an array or Traversable object, but' .
                ' "%s" given',
                is_object($repositories) ? get_class($repositories) : gettype($repositories)
            ));
        }
        foreach ($repositories as $repo) {
            $this->addRepository($repo);
        }
    }

    /**
     * Returns the stack
     *
     * @return \SplStack|MigrationRepositoryInterface[]
     */
    public function getRepositories()
    {
        return $this->stack;
    }

    /**
     * Resets the stack to the specified repositories
     *
     * @param array|\Traversable $repositories
     * @throws InvalidArgumentException
     */
    public function setRepositories($repositories)
    {
        if (!is_array($repositories) && (!is_object($repositories) || !$repositories instanceof \Traversable)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid argument provided for $repositories, expecting either an array or Traversable object, but' .
                ' "%s" given',
                is_object($repositories) ? get_class($repositories) : gettype($repositories)
            ));
        }
        $this->stack = new \SplStack();
        $this->addRepositories($repositories);
    }

    /**
     * Fetches all versions available to all repositories in the stack and returns them as a Linked collection.
     *
     * The returned collection contains versions groups sequentially into groups that correspond to each sub-repository.
     * Each of those groups is sorted with the repository's own comparator. Therefore, its strongly recommended not to
     * sort or modify the resulting collection.
     *
     * @return Collection
     */
    public function fetchAll()
    {
        $collection = new Collection();
        foreach ($this->getRepositories() as $repo) {
            /** @var MigrationRepositoryInterface $repo */
            $versions = $repo->fetchAll();
            $collection->merge($versions);
        }

        return $collection;
    }

    /**
     * Sets the migration factory for ALL repositories in the stack.
     *
     * @param FactoryInterface $factory
     */
    public function setMigrationFactory(FactoryInterface $factory)
    {
        foreach ($this->getRepositories() as $repo) {
            $repo->setMigrationFactory($factory);
        }
    }
}
