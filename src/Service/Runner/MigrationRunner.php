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

namespace Baleen\Migrations\Service\Runner;

use Baleen\Migrations\Exception\Service\Runner\RunnerException;
use Baleen\Migrations\Migration\OptionsInterface;
use Baleen\Migrations\Service\Runner\Event\Migration\MigrateAfterEvent;
use Baleen\Migrations\Service\Runner\Event\Migration\MigrateBeforeEvent;
use Baleen\Migrations\Shared\Event\Context\CollectionContext;
use Baleen\Migrations\Shared\Event\Context\CollectionContextInterface;
use Baleen\Migrations\Shared\Event\Context\ContextInterface;
use Baleen\Migrations\Shared\Event\Context\HasContextTrait;
use Baleen\Migrations\Shared\Event\Publisher\HasInternalPublisherTrait;
use Baleen\Migrations\Shared\Event\PublisherInterface;
use Baleen\Migrations\Version\VersionInterface;

/**
 * Class MigrationRunner
 *
 * A Runner that emits domain events using an Emitter
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
final class MigrationRunner implements MigrationRunnerInterface
{
    use HasInternalPublisherTrait;
    use HasContextTrait;

    /**
     * MigrationRunner constructor.
     *
     * @param PublisherInterface $publisher
     * @param CollectionContextInterface $context
     */
    public function __construct(
        PublisherInterface $publisher = null,
        CollectionContextInterface $context = null
    ) {
        if (null === $context) {
            $context = CollectionContext::createWithProgress(1, 1);
        }
        $this->setContext($context);

        $this->setPublisher($publisher);
    }

    /**
     * Runs a single version using the specified options
     *
     * @param VersionInterface $version
     * @param OptionsInterface $options
     *
     * @return false|MigrateAfterEvent
     *
     * @throws RunnerException
     */
    public function run(VersionInterface $version, OptionsInterface $options)
    {
        if (!$this->shouldMigrate($version, $options)) {
            if ($options->isExceptionOnSkip()) {
                throw new RunnerException(sprintf(
                    'Cowardly refusing to run %s() on a version that is already "%s" (ID: %s).',
                    $options->getDirection(),
                    $options->getDirection(),
                    $version->getId()
                ));
            }

            return false; // skip
        }

        // Dispatch MIGRATE_BEFORE
        $this->getPublisher()->publish(new MigrateBeforeEvent($version, $options, $this->getContext()));

        $version->migrate($options); // state will be changed

        // Dispatch MIGRATE_AFTER
        $event = new MigrateAfterEvent($version, $options, $this->getContext());
        $this->getPublisher()->publish($event);

        return $event;
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
        || ($options->getDirection()->isUp() ^ $version->isMigrated()); // direction is opposite to state
    }

    /**
     * @inheritdoc
     */
    final public function withContext(ContextInterface $context) {
        return new static($this->getPublisher(), $context);
    }
}
