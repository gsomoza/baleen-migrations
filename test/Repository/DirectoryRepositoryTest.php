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

use Baleen\Migrations\Exception\RepositoryException;
use Baleen\Migrations\Migration\Factory\FactoryInterface;
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Repository\DirectoryRepository;
use BaleenTest\Migrations\BaseTestCase;
use Mockery as m;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class DirectoryRepositoryTest extends BaseTestCase
{

    public function testInstanceOfRepositoryInterface()
    {
        $instance = new DirectoryRepository(__DIR__);
        $this->assertInstanceOf('Baleen\Migrations\Repository\RepositoryInterface', $instance);
    }

    public function testProvidesDefaultFactory()
    {
        $instance = new DirectoryRepository(__DIR__);
        $prop = new \ReflectionProperty($instance, 'factory');
        $prop->setAccessible(true);
        $this->assertInstanceOf(FactoryInterface::class, $prop->getValue($instance));
    }

    public function testDirectoryMustExist()
    {
        $this->setExpectedException('Baleen\Migrations\Exception\InvalidArgumentException');
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
        $instance = new DirectoryRepository($directory);
        $instance->setClassNameRegex($regex);
        $migrations = $instance->fetchAll();
        $this->assertCount($count, $migrations);
    }

    public function testFetchAllUsesCustomFactoryToCreateMigrations()
    {
        // get first test case onlyi (all valid)
        list($directory, $count) = $this->fetchAllProvider()[0];
        $factory = m::mock(FactoryInterface::class);
        $factory->shouldReceive('create')->andReturn(m::mock(MigrationInterface::class));

        $instance = new DirectoryRepository($directory);
        $instance->setMigrationFactory($factory);
        $instance->setClassNameRegex(DirectoryRepository::PATTERN_DEFAULT);
        $migrations = $instance->fetchAll();
        $factory->shouldHaveReceived('create')->times($count);
        $this->assertCount($count, $migrations);
    }

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

    /**
     * @param $return
     * @dataProvider doFetchResultIsNotLinkedCollectionProvider
     */
    public function testDoFetchResultIsNotLinkedCollection($return)
    {
        $instance = m::mock(DirectoryRepository::class)->shouldAllowMockingProtectedMethods()->makePartial();
        $instance->shouldReceive('doFetchAll')->once()->andReturn($return);
        $this->setExpectedException(RepositoryException::class, 'LinkedVersions');
        $instance->fetchAll();
    }

    public function doFetchResultIsNotLinkedCollectionProvider()
    {
        return [
            ['scalar'],
            [new \stdClass()],
        ];
    }
}
