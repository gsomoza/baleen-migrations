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
use Baleen\Migrations\Exception\RepositoryException;
use Baleen\Migrations\Migration\Factory\FactoryInterface;
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Repository\DirectoryRepository;
use Baleen\Migrations\Repository\RepositoryInterface;
use Baleen\Migrations\Version\Comparator\ComparatorInterface;
use BaleenTest\Migrations\BaseTestCase;
use Mockery as m;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class DirectoryRepositoryTest extends BaseTestCase
{
    /**
     * testConstructor
     *
     * @param $path
     * @param $pattern
     * @param $factory
     * @param $comparator
     * @param string|null $exception
     *
     * @dataProvider constructorProvider
     */
    public function testConstructor(
        $path,
        $pattern = DirectoryRepository::PATTERN_DEFAULT,
        $factory = null,
        $comparator = null,
        $exception = null
    ) {
        if ($exception !== null) {
            $this->setExpectedException($exception);
        }
        $instance = new DirectoryRepository($path, $pattern, $factory, $comparator);

        $factoryMethod = new \ReflectionMethod($instance, 'getMigrationFactory');
        $factoryMethod->setAccessible(true);
        $this->assertInstanceOf(FactoryInterface::class, $factoryMethod->invoke($instance));

        $comparatorMethod = new \ReflectionMethod($instance, 'getComparator');
        $comparatorMethod->setAccessible(true);
        $this->assertInstanceOf(ComparatorInterface::class, $comparatorMethod->invoke($instance));
    }

    /**
     * constructorProvider
     * @return array
     */
    public function constructorProvider()
    {
        return [
            [' ', DirectoryRepository::PATTERN_DEFAULT, null, null, InvalidArgumentException::class], // invalid path
            [false, DirectoryRepository::PATTERN_DEFAULT, null, null, InvalidArgumentException::class], // invalid path
            [null, DirectoryRepository::PATTERN_DEFAULT, null, null, InvalidArgumentException::class], // invalid path
            [0, DirectoryRepository::PATTERN_DEFAULT, null, null, InvalidArgumentException::class], // invalid path
            ['/this/is/not/a/dir', DirectoryRepository::PATTERN_DEFAULT, null, null, InvalidArgumentException::class], // invalid path
            [__DIR__], // valid path
            [__DIR__, '', null, null, InvalidArgumentException::class], // invalid pattern
            [__DIR__, 'newPattern'], // valid pattern
            [__DIR__, 'newPattern', m::mock(FactoryInterface::class)], // new factory
            [__DIR__, 'newPattern', null, m::mock(ComparatorInterface::class)], // new comparator
        ];
    }

    /**
     * testInstanceOfRepositoryInterface
     */
    public function testInstanceOfRepositoryInterface()
    {
        $instance = new DirectoryRepository(__DIR__);
        $this->assertInstanceOf(RepositoryInterface::class, $instance);
    }

    /**
     * testDirectoryMustExist
     */
    public function testDirectoryMustExist()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        new DirectoryRepository('/non/existent');
    }

    /**
     * @param $directory
     * @param $count
     *
     * @param string $regex
     * @dataProvider fetchAllProvider
     */
    public function testFetchAll($directory, $count, $regex = DirectoryRepository::PATTERN_DEFAULT)
    {
        $instance = new DirectoryRepository($directory, $regex);
        $migrations = $instance->fetchAll();
        $this->assertCount($count, $migrations);
    }

    /**
     * testFetchAllUsesCustomFactoryToCreateMigrations
     * @throws RepositoryException
     */
    public function testFetchAllUsesCustomFactoryToCreateMigrations()
    {
        // get first test case onlyi (all valid)
        list($directory, $count) = $this->fetchAllProvider()[0];
        /** @var FactoryInterface|m\Mock $factory */
        $factory = m::mock(FactoryInterface::class);
        $factory->shouldReceive('create')->andReturn(m::mock(MigrationInterface::class));

        $instance = new DirectoryRepository($directory);
        $instance->setMigrationFactory($factory);
        $migrations = $instance->fetchAll();
        $factory->shouldHaveReceived('create')->times($count);
        $this->assertCount($count, $migrations);
    }

    /**
     * fetchAllProvider
     * @return array
     */
    public function fetchAllProvider()
    {
        $migrationsBase = TEST_BASE_DIR . '/Migrations';
        return [
            [$migrationsBase . '/AllValid', 2],
            [$migrationsBase . '/NoneValid', 0],
            [$migrationsBase . '/CustomRegex', 1, '/Version_([0-9]+).*/'], // custom regex
            // recursive search - should find 4 because there are two migrations in the custom regex directory that
            // conform to the default pattern (to test that they should NOT be loaded with a custom regex)
            [$migrationsBase, 4],
        ];
    }
}
