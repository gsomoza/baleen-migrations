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

use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Migration\Repository\Mapper\Definition;
use Baleen\Migrations\Migration\Repository\Mapper\MigrationMapperInterface;
use Baleen\Migrations\Migration\Repository\Mapper\RepositoryMapperInterface;
use Baleen\Migrations\Migration\Repository\MigrationRepository;
use Baleen\Migrations\Common\Collection\CollectionInterface;
use Baleen\Migrations\Delta\Collection\Collection;
use Baleen\Migrations\Delta\Repository\VersionRepositoryInterface as VersionRepositoryInterface;
use Baleen\Migrations\Delta\DeltaId;
use Baleen\Migrations\Delta\DeltaInterface;
use BaleenTest\Migrations\BaseTestCase;
use Mockery as m;

/**
 * Class MigrationRepositoryTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class MigrationRepositoryTest extends BaseTestCase
{
    /**
     * @dataProvider doFetchResultIsNotLinkedCollectionProvider
     */
    public function testFetchAll()
    {
        /** @var MigrationMapperInterface|m\Mock $mapper */
        $mapper = m::mock(MigrationMapperInterface::class);
        $definitions = $this->buildDefinitions(range(1, 5));
        $mapper->shouldReceive(['fetchAll' => $definitions])->once();

        /** @var VersionRepositoryInterface|m\Mock $storage */
        $storage = m::mock(VersionRepositoryInterface::class);
        $versionIds = $this->buildVersionIds(range(3, 5));
        $storage->shouldReceive(['fetchAll' => $versionIds])->once();

        $repository = new MigrationRepository($storage, $mapper);
        $collection = $repository->fetchAll();

        $this->assertInstanceOf(CollectionInterface::class, $collection);
        $this->assertCount(5, $collection);
        $migrated = new Collection($collection->slice(3)); // elements 3 to 5
        $this->assertTrue($migrated->forAll(function ($index, DeltaInterface $v) {
            return $v->isMigrated();
        }), 'Expected elements 3 to 5 to be migrated.');
    }

    /**
     * buildDefinitions
     * @param array $ids
     * @return array
     */
    public function buildDefinitions(array $ids)
    {
        return array_map(
            function ($migrationMockName) {
                // its important to create these mocks inside the loop: mockery must create a new class for each mock
                /** @var MigrationInterface|m\Mock $migration */
                $migration = m::namedMock($migrationMockName, MigrationInterface::class);
                return new Definition($migration);
            },
            $this->getMigrationMockNames($ids)
        );
    }

    /**
     * doFetchResultIsNotLinkedCollectionProvider
     * @return array
     */
    public function doFetchResultIsNotLinkedCollectionProvider()
    {
        return [
            ['scalar'],
            [new \stdClass()],
        ];
    }

    /**
     * buildVersionIds
     * @param array $range
     * @return array
     */
    private function buildVersionIds(array $range)
    {
        return array_map(
            function ($migrationMockName) {
                // note that this "Migration$id" string must correspond to the same string in ::buildDefinitions
                return DeltaId::fromNative($migrationMockName);
            },
            $this->getMigrationMockNames($range)
        );
    }

    /**
     * getMigrationMockNames
     * @param array $ids
     * @return array
     */
    private function getMigrationMockNames(array $ids) {
        return array_map(function ($id) {
            return "Migration$id";
        }, $ids);
    }
}
