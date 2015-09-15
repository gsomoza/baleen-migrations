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
use Baleen\Migrations\Timeline\AbstractTimeline;
use Baleen\Migrations\Version\Collection\Resolver\FirstLastResolver;
use Baleen\Migrations\Version\Collection\SortableVersions;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 *
 * @method Timeline\TimelineEmitter getEmitter()
 */
class Timeline extends AbstractTimeline
{
    /**
     * @param Version|string $goalVersion
     * @param Options $options
     *
     * @return SortableVersions A collection of modified versions
     *
     * @throws MigrationMissingException
     */
    public function upTowards($goalVersion, Options $options = null)
    {
        if (null === $options) {
            $options = new Options(Options::DIRECTION_UP);
            $options->setExceptionOnSkip(false);
        }
        $options->setDirection(Options::DIRECTION_UP); // make sure its right

        return $this->runCollection($goalVersion, $options, $this->versions);
    }

    /**
     * @param Version|string $goalVersion
     * @param Options $options
     *
     * @return SortableVersions A collection of modified versions
     *
     * @throws \Exception
     */
    public function downTowards($goalVersion, Options $options = null)
    {
        if (null === $options) {
            $options = new Options(Options::DIRECTION_DOWN);
            $options->setExceptionOnSkip(false);
        }
        $options->setDirection(Options::DIRECTION_DOWN); // make sure its right

        // get the goal version now, before reversing the collection
        $goalVersion = $this->versions->get($goalVersion);

        return $this->runCollection($goalVersion, $options, $this->versions->getReverse());
    }

    /**
     * Runs migrations up/down so that all versions *before and including* the specified version are "up" and
     * all versions *after* the specified version are "down".
     *
     * @param $goalVersion
     * @param \Baleen\Migrations\Migration\Options $options
     *
     * @return SortableVersions
     */
    public function goTowards($goalVersion, Options $options = null)
    {
        if (null === $options) {
            $options = new Options(Options::DIRECTION_UP);
            $options->setExceptionOnSkip(false);
        }
        $this->versions->rewind();
        $this->upTowards($goalVersion, $options);
        $this->versions->next(); // advance to the next element...
        $newGoal = $this->versions->current(); // ... and make it the goal for downTowards
        if ($newGoal !== false) { // unless we're at the end of the queue (no migrations can go down)
            $this->downTowards($newGoal, $options);
        }

        return $this->versions;
    }

    /**
     * @param Version|string $version
     * @param Options        $options
     * @param Progress       $progress Provides contextual information about current progress if this
     *                                 migration is one of many that are being run in batch.
     *
     * @return Version|false
     *
     * @throws TimelineException
     */
    public function runSingle($version, Options $options, Progress $progress = null)
    {
        $version = $this->versions->getOrException($version);
        // migration will always exist because its enforced in LinkedCollection
        $migration = $version->getMigration();

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
