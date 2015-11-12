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
 * <https://github.com/baleen/migrations>.
 */

namespace Baleen\Migrations\Timeline;

use Baleen\Migrations\Event\HasEmitterTrait;
use Baleen\Migrations\Event\Timeline\Progress;
use Baleen\Migrations\Exception\InvalidArgumentException;
use Baleen\Migrations\Migration\Command\MigrateCommand;
use Baleen\Migrations\Migration\Command\MigrationBusFactory;
use Baleen\Migrations\Migration\Command\MigrationBusInterface;
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Migration\OptionsInterface;
use Baleen\Migrations\Version\Collection\Linked;
use Baleen\Migrations\Version\VersionInterface;

/**
 * Encapsulates the lower-level methods of a Timeline, leaving the actual timeline logic to the extending class.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 *
 * @method TimelineEmitter getEmitter()
 */
abstract class AbstractTimeline implements TimelineInterface
{
    use HasEmitterTrait;

    /** @var MigrationBusInterface */
    private $migrationBus;

    /** @var Linked */
    private $versions;

    /**
     * @param Linked $versions
     * @param MigrationBusInterface $migrationBus A CommandBus that will be used to run each individual migration.
     */
    public function __construct(Linked $versions, MigrationBusInterface $migrationBus = null)
    {
        if (null === $migrationBus) {
            $migrationBus = MigrationBusFactory::create();
        }
        $this->migrationBus = $migrationBus;

        $this->versions = $versions;
    }

    /**
     * Returns true if the operation is forced, or if the direction is the opposite to the state of the migration.
     *
     * @param VersionInterface $version
     * @param OptionsInterface $options
     *
     * @return bool
     */
    protected function shouldMigrate(VersionInterface $version, OptionsInterface $options)
    {
        return $options->isForced()
        || ($options->isDirectionUp() ^ $version->isMigrated()); // direction is opposite to state
    }

    /**
     * Must create and return a default specialised dispatcher.
     *
     * @return \Baleen\Migrations\Event\EmitterInterface
     */
    protected function createEmitter()
    {
        return new TimelineEmitter();
    }

    /**
     * @param MigrationInterface $migration
     * @param OptionsInterface $options
     *
     * @return bool
     */
    protected function doRun(MigrationInterface $migration, OptionsInterface $options)
    {
        $command = new MigrateCommand($migration, $options);
        $this->migrationBus->handle($command);
    }

    /**
     * Executes migrations against a collection
     *
     * @param VersionInterface $goal
     * @param OptionsInterface $options
     * @param Linked $collection
     *
     * @return Linked
     *
     * @throws InvalidArgumentException
     */
    protected function runCollection(VersionInterface $goal, OptionsInterface $options, Linked $collection)
    {
        $current = 1;
        $progress = new Progress(max($collection->count(), 1), $current);

        // dispatch COLLECTION_BEFORE
        $this->getEmitter()->dispatchCollectionBefore($goal, $options, $collection, $progress);

        $modified = new Linked();
        $comparator = $collection->getComparator();

        // TODO: add tests to see if rewind is necessary
        $collection->first(); // rewind
        foreach ($collection as $version) {
            $progress->setCurrent($current);
            $result = $this->runSingle($version, $options, $progress);
            if ($result) {
                $modified->add($version);
            }
            if ($comparator($version, $goal) >= 0) {
                break;
            }
            $current += 1;
        }

        // dispatch COLLECTION_AFTER
        $this->getEmitter()->dispatchCollectionAfter($goal, $options, $modified, $progress);

        return $modified;
    }

    /**
     * getVersions
     * @return Linked
     */
    final public function getVersions()
    {
        return $this->versions;
    }
}
