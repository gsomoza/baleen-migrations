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

namespace BaleenTest\Migrations\Repository;

use Baleen\Migrations\Exception\InvalidArgumentException;
use Baleen\Migrations\Migration\Factory\FactoryInterface;
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Repository\RepositoryInterface;
use Baleen\Migrations\Repository\RepositoryStack;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version\Collection\Linked;
use Baleen\Migrations\Version\Collection\Sortable;
use BaleenTest\Migrations\BaseTestCase;
use Mockery as m;

/**
 * Class RepositoryStackTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class RepositoryStackTest extends BaseTestCase
{
    /**
     * testConstructor
     */
    public function testConstructor()
    {
        $instance = new RepositoryStack();
        $repositories = $instance->getRepositories();
        $this->assertInstanceOf(\Traversable::class, $repositories);
        $this->assertInstanceOf(\ArrayAccess::class, $repositories);
        $this->assertInstanceOf(\Countable::class, $repositories);
        $this->assertCount(0, $repositories);
    }

    /**
     * testAddRepositoroy
     */
    public function testAddRepository()
    {
        $instance = new RepositoryStack();
        /** @var RepositoryInterface|m\Mock $repo */
        $repo = m::mock(RepositoryInterface::class);
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
        $instance = new RepositoryStack();
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
        $mock1 = m::mock(RepositoryInterface::class);
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
        /** @var RepositoryStack|m\Mock $instance */
        $instance = m::mock(new RepositoryStack())
            ->shouldAllowMockingProtectedMethods();

        // load the instance with some previous repos, which should be overwritten
        /** @var RepositoryInterface|m\Mock $tmpRepo */
        $tmpRepo = m::mock(RepositoryInterface::class);
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

        /** @var RepositoryStack|m\Mock $instance */
        $instance = m::mock(new RepositoryStack())
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        /** @var RepositoryInterface|m\Mock $repo */
        $repo = m::mock(RepositoryInterface::class);
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
        /** @var RepositoryStack|m\Mock $instance */
        $instance = new RepositoryStack();
        $instance->setRepositories($repositories);

        $result = $instance->fetchAll();
        $this->assertInstanceOf(Linked::class, $result);
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
        /** @var MigrationInterface|m\Mock $migration */
        $migration = m::mock(MigrationInterface::class);
        $version1 = new Version(1);
        $version1->setMigration($migration);
        $version2 = new Version(2);
        $version2->setMigration($migration);
        $version3 = new Version(3);
        $version3->setMigration($migration);
        $versions1 = new Sortable([$version1, $version3]);
        $versions2 = new Sortable([$version1, $version2]);

        $repo1 = m::mock(RepositoryInterface::class);
        $repo1->shouldReceive('fetchAll')->zeroOrMoreTimes()->andReturn($versions1);
        $repo2 = m::mock(RepositoryInterface::class);
        $repo2->shouldReceive('fetchAll')->zeroOrMoreTimes()->andReturn($versions2);

        return [
            [[]],
            [[$repo1, $repo2], 3] // four versions total, but two are repeated
        ];
    }
}
