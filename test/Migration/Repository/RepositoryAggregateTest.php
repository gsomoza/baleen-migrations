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

namespace BaleenTest\Migrations\Migration\Repository;

use Baleen\Migrations\Exception\InvalidArgumentException;
use Baleen\Migrations\Migration\Factory\FactoryInterface;
use Baleen\Migrations\Migration\Repository\AggregateMigrationRepository;
use Baleen\Migrations\Migration\Repository\MigrationRepositoryInterface;
use Baleen\Migrations\Delta\Collection\Collection;
use BaleenTest\Migrations\BaseTestCase;
use Mockery as m;

/**
 * Class RepositoryAggregateTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class RepositoryAggregateTest extends BaseTestCase
{
    /**
     * testConstructor
     */
    public function testConstructor()
    {
        $instance = new AggregateMigrationRepository();
        $repositories = $instance->getRepositories();
        $this->assertInstanceOf(\Traversable::class, $repositories);
        $this->assertInstanceOf(\ArrayAccess::class, $repositories);
        $this->assertInstanceOf(\Countable::class, $repositories);
        $this->assertCount(0, $repositories);
    }

    /**
     * testAddRepository
     */
    public function testAddRepository()
    {
        $instance = new AggregateMigrationRepository();
        /** @var MigrationRepositoryInterface|m\Mock $repo */
        $repo = m::mock(MigrationRepositoryInterface::class);
        $instance->addRepository($repo);
        $this->assertNotEmpty($instance->getRepositories());
        $this->assertSame($repo, $instance->getRepositories()[0]);
    }

    /**
     * testAddRepositories
     * @param $repositories
     * @param null $exception
     * @param int $count
     * @throws InvalidArgumentException
     * @dataProvider repositoriesProvider
     */
    public function testAddRepositories($repositories, $count = 0, $exception = null)
    {
        $instance = new AggregateMigrationRepository();
        if (!empty($exception)) {
            $this->setExpectedException($exception);
        }
        $instance->addRepositories($repositories);
        $this->assertCount($count, $instance->getRepositories());
    }

    /**
     * repositoriesProvider
     * @return array
     */
    public function repositoriesProvider()
    {
        $mock1 = m::mock(MigrationRepositoryInterface::class);
        $mock2 = clone $mock1;
        $splStack = new \SplStack();
        $splStack[] = $mock1;
        return [
            [[]],
            [[$mock1, $mock2], 2],
            [$splStack, 1],
            ['notTraversable', 0, InvalidArgumentException::class]
        ];
    }

    /**
     * testSetRepositories
     *
     * @param $repositories
     * @param int $count
     * @param null $exception
     *
     * @throws InvalidArgumentException
     * @dataProvider repositoriesProvider
     */
    public function testSetRepositories($repositories, $count = 0, $exception = null)
    {
        /** @var AggregateMigrationRepository|m\Mock $instance */
        $instance = m::mock(new AggregateMigrationRepository())
            ->shouldAllowMockingProtectedMethods();

        // load the instance with some previous repos, which should be overwritten
        /** @var MigrationRepositoryInterface|m\Mock $tmpRepo */
        $tmpRepo = m::mock(MigrationRepositoryInterface::class);
        $instance->addRepository($tmpRepo);

        if (!empty($exception)) {
            $this->setExpectedException($exception);
        }

        $instance->setRepositories($repositories);
        $this->assertCount($count, $instance->getRepositories());
    }

    /**
     * testSetMigrationFactory
     */
    public function testSetMigrationFactory()
    {
        /** @var FactoryInterface|m\Mock $factory */
        $factory = m::mock(FactoryInterface::class);

        /** @var AggregateMigrationRepository|m\Mock $instance */
        $instance = m::mock(new AggregateMigrationRepository())
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        /** @var MigrationRepositoryInterface|m\Mock $repo */
        $repo = m::mock(MigrationRepositoryInterface::class);
        $repo->shouldReceive('setMigrationFactory')->once()->with($factory);
        $instance->addRepository($repo);

        $instance->setMigrationFactory($factory);
    }

    /**
     * testFetchAll
     * @dataProvider fetchAllProvider
     * @param $repositories
     * @param int $count
     * @throws InvalidArgumentException
     */
    public function testFetchAll($repositories, $count = 0)
    {
        /** @var AggregateMigrationRepository|m\Mock $instance */
        $instance = new AggregateMigrationRepository();
        $instance->setRepositories($repositories);

        $result = $instance->fetchAll();
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount($count, $result);
        foreach ($repositories as $repo) {
            /** @var m\Mock $repo */
            $repo->shouldHaveReceived('fetchAll')->once();
        }
    }

    /**
     * fetchAllProvider
     * @return array
     */
    public function fetchAllProvider()
    {
        list($version1, $version2, $version3) = $this->buildVersions(range(1, 3));
        $versions1 = new Collection([$version1, $version3]);
        $versions2 = new Collection([$version1, $version2]);

        /** @var MigrationRepositoryInterface|m\Mock $repo1 */
        $repo1 = m::mock(MigrationRepositoryInterface::class);
        $repo1->shouldReceive('fetchAll')->zeroOrMoreTimes()->andReturn($versions1);
        /** @var MigrationRepositoryInterface|m\Mock $repo2 */
        $repo2 = m::mock(MigrationRepositoryInterface::class);
        $repo2->shouldReceive('fetchAll')->zeroOrMoreTimes()->andReturn($versions2);

        return [
            [[]],
            [[$repo1, $repo2], 3] // four versions total, but two are repeated
        ];
    }
}
