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

use Baleen\Migrations\Event\Timeline\Progress;
use Baleen\Migrations\Exception\MigrationMissingException;
use Baleen\Migrations\Exception\TimelineException;
use Baleen\Migrations\Migration\Options;
use Baleen\Migrations\Migration\OptionsInterface;
use Baleen\Migrations\Timeline\AbstractTimeline;
use Baleen\Migrations\Version\Collection\Linked;
use Baleen\Migrations\Version\Collection\Sortable;
use Baleen\Migrations\Version\VersionInterface;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 *
 * @method Timeline\TimelineEmitter getEmitter()
 */
final class Timeline extends AbstractTimeline
{
    /**
     * @param VersionInterface $goal
     * @param OptionsInterface $options
     *
     * @return Sortable A collection of modified versions
     *
     * @throws MigrationMissingException
     */
    public function upTowards(VersionInterface $goal, OptionsInterface $options = null)
    {
        if (null === $options) {
            $options = new Options(OptionsInterface::DIRECTION_UP);
            $options = $options->withExceptionOnSkip(false);
        } else {
            $options = $options->withDirection(OptionsInterface::DIRECTION_UP); // make sure its right
        }

        // get only versions that are not migrated and are lesser than or equal to the goal version
        $collection = $this->getVersions();
        $comparator = $collection->getComparator();
        $collection = $collection->filter(function (VersionInterface $v) use ($goal, $comparator) {
            return !$v->isMigrated() && $comparator($v, $goal) <= 0;
        });
        /** @var Linked $collection */
        $collection = $collection->sort($comparator);

        return $this->runCollection($goal, $options, $collection);
    }

    /**
     * @param VersionInterface $goal
     * @param OptionsInterface $options
     *
     * @return Sortable A collection of modified versions
     *
     * @throws \Exception
     */
    public function downTowards(VersionInterface $goal, OptionsInterface $options = null)
    {
        if (null === $options) {
            $options = new Options(OptionsInterface::DIRECTION_DOWN);
            $options = $options->withExceptionOnSkip(false);
        } else {
            $options = $options->withDirection(OptionsInterface::DIRECTION_DOWN); // make sure its right
        }

        // get only versions that are not migrated and are lesser than or equal to the goal version
        $collection = $this->getVersions()->getReverse();
        $comparator = $collection->getComparator(); // already reversed
        $collection = $collection->filter(function (VersionInterface $v) use ($goal, $comparator) {
            return $v->isMigrated() && $comparator($v, $goal) <= 0;
        });
        /** @var Linked $collection */
        $collection = $collection->sort($comparator);

        return $this->runCollection($goal, $options, $collection);
    }

    /**
     * Runs migrations up/down so that all versions *before and including* the specified version are "up" and
     * all versions *after* the specified version are "down".
     *
     * @param VersionInterface $goalUp
     * @param OptionsInterface $options
     *
     * @return Linked A collection of versions that were *changed* during the process. Note that this collection may
     *                significantly defer from what would be obtained by $this->getVersions()
     */
    public function goTowards(VersionInterface $goalUp, OptionsInterface $options = null)
    {
        $collection = $this->getVersions();
        // create a new collection to store the changed versions
        $changed = clone $collection; // this ensures we keep the same comparator
        $changed->clear();

        $goalIndex = $collection->indexOf($goalUp);
        $goalDown = $collection->get($goalIndex + 1, false);

        $changedUp = $this->upTowards($goalUp, $options);
        $changed->merge($changedUp);

        if (null !== $goalDown) { // unless we're at the end of the queue (no migrations can go down)
            $changedDown = $this->downTowards($goalDown, $options);
            $changed->merge($changedDown);
        }

        $changed->sort();

        return $changed;
    }

    /**
     * @param VersionInterface $version
     * @param OptionsInterface $options
     * @param Progress $progress Provides contextual information about current progress if this
     *                           migration is one of many that are being run in batch.
     *
     * @return VersionInterface|false
     *
     * @throws TimelineException
     */
    public function runSingle(VersionInterface $version, OptionsInterface $options, Progress $progress = null)
    {
        $migration = $version->getMigration();
        if (!$migration) {
            throw new TimelineException('Invalid version specified: version must be linked to a migration object.');
        }

        if (!$this->shouldMigrate($version, $options)) {
            if ($options->isExceptionOnSkip()) {
                throw new TimelineException(sprintf(
                    'Cowardly refusing to run %s() on a version that is already "%s" (ID: %s).',
                    $options->getDirection(),
                    $options->getDirection(),
                    $version->getId()
                ));
            }

            return false; // skip
        }

        // Dispatch MIGRATE_BEFORE
        $this->getEmitter()->dispatchMigrationBefore($version, $options, $progress);

        $this->doRun($migration, $options);

        // won't get executed if an exception is thrown
        $version->setMigrated($options->isDirectionUp());

        // Dispatch MIGRATE_AFTER
        $this->getEmitter()->dispatchMigrationAfter($version, $options, $progress);

        return $version;
    }
}
