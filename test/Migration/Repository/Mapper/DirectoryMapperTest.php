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

namespace BaleenTest\Migrations\Migration\Repository\Mapper;

use Baleen\Migrations\Exception\InvalidArgumentException;
use Baleen\Migrations\Exception\Migration\Repository\RepositoryException;
use Baleen\Migrations\Migration\Factory\FactoryInterface;
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Migration\Repository\Mapper\DirectoryMapper;
use Baleen\Migrations\Migration\Repository\Mapper\RepositoryMapperInterface;
use BaleenTest\Migrations\BaseTestCase;
use Mockery as m;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class DirectoryMapperTest extends BaseTestCase
{
    /**
     * testConstructor
     *
     * @param $path
     * @param string $pattern
     * @param string|null $exception
     *
     * @dataProvider constructorProvider
     */
    public function testConstructor(
        $path,
        $pattern = DirectoryMapper::PATTERN_DEFAULT,
        $exception = null
    ) {
        if ($exception !== null) {
            $this->setExpectedException($exception);
        }
        /** @var FactoryInterface $factory */
        $factory = m::mock(FactoryInterface::class);
        $mapper = new DirectoryMapper($path, $factory, $pattern);

        $this->assertInstanceOf(RepositoryMapperInterface::class, $mapper);
    }

    /**
     * constructorProvider
     * @return array
     */
    public function constructorProvider()
    {
        return [
            [' ', DirectoryMapper::PATTERN_DEFAULT, InvalidArgumentException::class], // invalid path
            [false, DirectoryMapper::PATTERN_DEFAULT, InvalidArgumentException::class], // invalid path
            [null,DirectoryMapper::PATTERN_DEFAULT, InvalidArgumentException::class], // invalid path
            [0, DirectoryMapper::PATTERN_DEFAULT, InvalidArgumentException::class], // invalid path
            ['/this/is/not/a/dir', DirectoryMapper::PATTERN_DEFAULT, InvalidArgumentException::class], // invalid path
            [__DIR__], // valid path
            [__DIR__, '', InvalidArgumentException::class], // new invalid pattern
            [__DIR__, 'newPattern'], // new valid pattern
        ];
    }

    /**
     * testInstanceOfRepositoryInterface
     */
    public function testInstanceOfRepositoryInterface()
    {
        $instance = new DirectoryMapper(__DIR__);
        $this->assertInstanceOf(RepositoryMapperInterface::class, $instance);
    }

    /**
     * testDirectoryMustExist
     */
    public function testDirectoryMustExist()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        new DirectoryMapper('/non/existent');
    }

    /**
     * @param $directory
     * @param $count
     * @param string $regex
     * @param null|m\Mock $factory
     *
     * @dataProvider fetchAllProvider
     */
    public function testFetchAll($directory, $count, $regex = DirectoryMapper::PATTERN_DEFAULT, $factory = null)
    {
        if (null !== $factory) {
            $migration = m::mock(MigrationInterface::class);
            $factory->shouldReceive('create')->times($count)->with(m::type('string'))->andReturn($migration);
        }
        $instance = new DirectoryMapper($directory, $factory, $regex);
        $migrations = $instance->fetchAll();
        $this->assertCount($count, $migrations);
    }

    /**
     * testFetchAllUsesCustomFactoryToCreateMigrations
     * @throws RepositoryException
     */
    public function testFetchAllUsesCustomFactoryToCreateMigrations()
    {
        // get first test case only (all valid)
        list($directory, $count) = $this->fetchAllProvider()[0];

        $instance = new DirectoryMapper($directory);
        $migrations = $instance->fetchAll();
        $this->assertCount($count, $migrations);
    }

    /**
     * fetchAllProvider
     * @return array
     */
    public function fetchAllProvider()
    {
        /** @var FactoryInterface|m\Mock $factory */
        $factory = m::mock(FactoryInterface::class);
        $migrationsBase = TEST_BASE_DIR . '/Fixtures/Migrations';
        return [
            [$migrationsBase . '/AllValid', 2],
            [$migrationsBase . '/AllValid', 2, DirectoryMapper::PATTERN_DEFAULT, $factory], // custom factory
            [$migrationsBase . '/NoneValid', 0],
            [$migrationsBase . '/CustomRegex', 1, '/Version_([0-9]+).*/'], // custom regex
            // recursive search - should find 4 because there are two migrations in the custom regex directory that
            // conform to the default pattern (to test that they should NOT be loaded with a custom regex)
            [$migrationsBase, 4],
        ];
    }
}
