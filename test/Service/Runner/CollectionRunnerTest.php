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

namespace BaleenTest\Migrations\Service\Runner;

use Baleen\Migrations\Migration\Options;
use Baleen\Migrations\Migration\Options\Direction;
use Baleen\Migrations\Service\Runner\CollectionRunner;
use Baleen\Migrations\Service\Runner\Event\Collection\CollectionAfterEvent;
use Baleen\Migrations\Service\Runner\Event\Collection\CollectionBeforeEvent;
use Baleen\Migrations\Service\Runner\Event\Migration\MigrateBeforeEvent;
use Baleen\Migrations\Service\Runner\Event\Migration\MigrationEvent;
use Baleen\Migrations\Shared\Event\PublisherInterface;
use Baleen\Migrations\Version\Collection\Collection;
use Baleen\Migrations\Version\Comparator\IdComparator;
use Baleen\Migrations\Version\VersionInterface;
use Mockery as m;

/**
 * Class CollectionRunnerTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class CollectionRunnerTest extends RunnerTestCase
{
    /**
     * testRunCollection
     * @param $versions
     * @param $goal
     * @param Direction $direction
     * @param $resultMatrix
     * @throws \Exception
     * @dataProvider runCollectionProvider
     */
    public function testRunCollection($versions, $goal, Direction $direction, $resultMatrix)
    {
        switch ($versions) {
            case 'all-migrated':
                $versions = $this->getAllMigratedVersionsFixture();
                break;
            case 'no-migrated':
                $versions = $this->getNoMigratedVersionsFixture();
                break;
            case 'mixed-migrated':
                $versions = $this->getMixedVersionsFixture();
                break;
            default:
                throw new \Exception('invalid key specified for this test!');
        }

        $collection = new Collection($versions, null, new IdComparator());
        $target = $collection->find($goal);

        /** @var PublisherInterface|m\Mock $publisher */
        $publisher = m::mock(PublisherInterface::class);
        $publisher->shouldReceive('publish')
            ->with(m::type(CollectionBeforeEvent::class))
            ->once();
        $publisher->shouldReceive('publish')
            ->with(m::type(CollectionAfterEvent::class))
            ->once();

        $previousProgress = 0;
        $publisher->shouldReceive('publish')
            ->with(m::on(function ($val) use (&$previousProgress) {
                if (!$val instanceof MigrationEvent) {
                    return false;
                }
                $progress = $val->getContext()->getProgress();

                // check that progress increments for every new migration event
                if ($val instanceof MigrateBeforeEvent) {
                    $hasProgressed = $progress->getCurrent() > $previousProgress;
                } else {
                    $hasProgressed = true;
                    $previousProgress = $progress->getCurrent();
                }
                return $progress->getTotal() == 12 && $hasProgressed;
            }))
            ->zeroOrMoreTimes(); // we're not testing this

        $runner = new CollectionRunner($collection, null, $publisher);
        $options = (new Options($direction))->withExceptionOnSkip(false);
        $runner->run($target, $options);

        // ASSERTS
        $map = $collection->map(function(VersionInterface $v) {
            return $v->isMigrated();
        });
        $resultMatrix = array_map(function($item) {
            return (bool) $item;
        }, $resultMatrix);
        $this->assertEquals($resultMatrix, array_values($map));
    }

    /**
     * runCollectionProvider
     * NOTE: testRunCollection expects each matrix to have exactly 12 items
     *
     * @return array
     */
    public function runCollectionProvider()
    {
        $allMigrated = 'all-migrated';
        $noMigrated = 'no-migrated';
        $mixedMigrated = 'mixed-migrated';

        // collection, goal, direction, up/down matrix
        return [
            // last, up
            [$allMigrated, 'last', Direction::up(), array_fill(0, 12, 1)],
            [$noMigrated, 'last', Direction::up(), array_fill(0, 12, 1)],
            [$mixedMigrated, 'last', Direction::up(), array_fill(0, 12, 1)],
            // last, down
            [$allMigrated, 'last', Direction::down(), array_fill(0, 12, 0)],
            [$noMigrated, 'last', Direction::down(), array_fill(0, 12, 0)],
            [$mixedMigrated, 'last', Direction::down(), array_fill(0, 12, 0)],
            // first, up
            [$allMigrated, 'first', Direction::up(), array_fill(0, 12, 1)],
            [$noMigrated, 'first', Direction::up(), [1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]],
            [$mixedMigrated, 'first', Direction::up(), $this->getUpdatedMixedMatrix([0], 1)],
            // first, down
            [$allMigrated, 'first', Direction::down(), [0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1]],
            [$noMigrated, 'first', Direction::down(), array_fill(0, 12, 0)],
            [$mixedMigrated, 'first', Direction::down(), $this->getUpdatedMixedMatrix([0], 0)],
            // v08, up
            [$allMigrated, 'v06', Direction::up(), array_fill(0, 12, 1)],
            [$noMigrated, 'v06', Direction::up(), [1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0]],
            [$mixedMigrated, 'v06', Direction::up(), $this->getUpdatedMixedMatrix(range(0, 5), 1)],
            // v08, down
            [$allMigrated, 'v06', Direction::down(), [0, 0, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1]],
            [$noMigrated, 'v06', Direction::down(), array_fill(0, 12, 0)],
            [$mixedMigrated, 'v06', Direction::down(), $this->getUpdatedMixedMatrix(range(0, 5), 0)],
        ];
    }
}
