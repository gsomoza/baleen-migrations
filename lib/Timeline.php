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

namespace Baleen\Migrations;

use Baleen\Migrations\Event\HasSpecialisedDispatcherTrait;
use Baleen\Migrations\Event\SpecialisedDispatcher;
use Baleen\Migrations\Exception\MigrationException;
use Baleen\Migrations\Exception\MigrationMissingException;
use Baleen\Migrations\Migration\Command\MigrationBusFactory;
use Baleen\Migrations\Migration\Command\MigrateCommand;
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Migration\MigrateOptions;
use Baleen\Migrations\Timeline\TimelineDispatcher;
use Baleen\Migrations\Timeline\TimelineInterface;
use Baleen\Migrations\Version\Collection;
use Baleen\Migrations\Version\Comparator\DefaultComparator;
use League\Tactician\CommandBus;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 *
 * @method TimelineDispatcher getDispatcher()
 */
class Timeline implements TimelineInterface
{
    use HasSpecialisedDispatcherTrait;

    /** @var string[] */
    protected $allowedDirections;

    /** @var Collection */
    protected $versions;

    /** @var callable */
    protected $comparator;

    /** @var CommandBus */
    protected $migrationBus;

    /**
     * @param array|Collection $versions
     * @param callable         $comparator
     */
    public function __construct($versions, callable $comparator = null)
    {
        $this->migrationBus = MigrationBusFactory::create();

        if (is_array($versions)) {
            $versions = new Collection($versions);
        }
        if (null === $comparator) {
            $comparator = new DefaultComparator();
        }
        $versions->sortWith($comparator);
        $this->comparator = $comparator;
        $this->versions = $versions;
    }

    /**
     * @param Version|string $goalVersion
     * @param MigrateOptions $options
     *
     * @throws MigrationMissingException
     */
    public function upTowards($goalVersion, MigrateOptions $options = null)
    {
        if (null === $options) {
            $options = new MigrateOptions(MigrateOptions::DIRECTION_UP);
            $options->setExceptionOnSkip(false);
        }
        $options->setDirection(MigrateOptions::DIRECTION_UP); // make sure its right

        $this->runCollection($goalVersion, $options, $this->versions);
    }

    /**
     * @param Version|string $goalVersion
     * @param MigrateOptions $options
     *
     * @throws \Exception
     */
    public function downTowards($goalVersion, MigrateOptions $options = null)
    {
        if (null === $options) {
            $options = new MigrateOptions(MigrateOptions::DIRECTION_DOWN);
            $options->setExceptionOnSkip(false);
        }
        $options->setDirection(MigrateOptions::DIRECTION_DOWN); // make sure its right
        $this->runCollection($goalVersion, $options, $this->versions->getReverse());
    }

    /**
     * Runs migrations up/down so that all versions *before and including* the specified version are "up" and
     * all versions *after* the specified version are "down".
     *
     * @param $goalVersion
     * @param \Baleen\Migrations\Migration\MigrateOptions $options
     *
     * @return mixed
     */
    public function goTowards($goalVersion, MigrateOptions $options = null)
    {
        if (null === $options) {
            $options = new MigrateOptions(MigrateOptions::DIRECTION_UP);
            $options->setExceptionOnSkip(false);
        }
        $this->versions->rewind();
        $this->upTowards($goalVersion, $options);
        $this->versions->next(); // advance to the next element...
        $newGoal = $this->versions->current(); // ... and make it the goal for downTowards
        if ($newGoal !== false) { // unless we're at the end of the queue (no migrations can go down)
            $this->downTowards($newGoal, $options);
        }
    }

    /**
     * @param \Baleen\Migrations\Version $version
     * @param MigrateOptions             $options
     *
     * @throws MigrationException
     */
    public function runSingle($version, MigrateOptions $options)
    {
        $migration = $version->getMigration();
        if (null === $migration) {
            throw new MigrationException(
                'Migration object missing for registered version "%s".',
                $version->getId()
            );
        }
        $isMigratedResult = $version->isMigrated();
        $skip = false;
        $exceptionMessage = false;
        switch ($options->getDirection()) {
            case MigrateOptions::DIRECTION_UP:
                $isMigratedResult = true;
                $skip = !$options->isForced() && $version->isMigrated();
                if ($skip && $options->isExceptionOnSkip()) {
                    $exceptionMessage = sprintf(
                        'Cowardly refusing to run up() on a version that has already been migrated (%s).',
                        $version->getId()
                    );
                }
                break;

            case MigrateOptions::DIRECTION_DOWN:
                $isMigratedResult = false;
                $skip = !$options->isForced() && !$version->isMigrated();
                if ($skip && $options->isExceptionOnSkip()) {
                    $exceptionMessage = sprintf(
                        'Cowardly refusing to run up() on a version that has already been migrated (%s).',
                        $version->getId()
                    );
                }
                break;
            default:
        }

        if ($exceptionMessage !== false) {
            throw new MigrationException($exceptionMessage);
        }

        if ($skip) {
            return;
        }

        // Dispatch MIGRATE_BEFORE
        $this->getDispatcher()->dispatchMigrationBefore($version, $options);

        $this->doRun($migration, $options);
        $version->setMigrated($isMigratedResult); // won't get executed if an exception is thrown

        // Dispatch MIGRATE_AFTER
        $this->getDispatcher()->dispatchMigrationAfter($version, $options);
    }

    /**
     * @param MigrationInterface $migration
     * @param MigrateOptions     $options
     *
     * @return bool
     */
    protected function doRun(MigrationInterface $migration, MigrateOptions $options)
    {
        $command = new MigrateCommand($migration, $options);
        $this->migrationBus->handle($command);
    }

    /**
     * Must create and return a default specialised dispatcher
     *
     * @return SpecialisedDispatcher
     */
    protected function createDefaultDispatcher()
    {
        return new TimelineDispatcher();
    }

    /**
     * @param $goalVersion
     * @param MigrateOptions $options
     * @param $collection
     * @throws MigrationException
     */
    protected function runCollection($goalVersion, MigrateOptions $options, Collection $collection)
    {
        $goalVersion = $this->versions->getOrException($goalVersion);

        // dispatch COLLECTION_BEFORE
        $this->getDispatcher()->dispatchCollectionBefore($goalVersion, $options, $collection);

        foreach ($collection as $version) {
            $this->runSingle($version, $options);
            $goalReached = call_user_func($this->comparator, $goalVersion, $version) === 0;
            if ($goalReached) {
                break;
            }
        }

        // dispatch COLLECTION_AFTER
        $this->getDispatcher()->dispatchCollectionAfter($goalVersion, $options, $collection);
    }
}
