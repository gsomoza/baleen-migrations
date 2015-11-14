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

use Baleen\Migrations\Migration\OptionsInterface;
use Baleen\Migrations\Service\Runner\Event\Collection\CollectionAfterEvent;
use Baleen\Migrations\Service\Runner\Event\Collection\CollectionBeforeEvent;
use Baleen\Migrations\Shared\Collection\CollectionInterface;
use Baleen\Migrations\Shared\Event\Context\CollectionContext;
use Baleen\Migrations\Shared\Event\PublisherInterface;
use Baleen\Migrations\Version\Collection\Collection;
use Baleen\Migrations\Version\VersionInterface;

/**
 * Class CollectionRunner
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
final class CollectionRunner extends AbstractRunner
{
    /** @var CollectionInterface */
    private $collection;

    /** @var MigrationRunner */
    private $migrationRunner;

    /**
     * CollectionRunner constructor.
     * @param CollectionInterface $collection
     * @param RunnerInterface $migrationRunner Will be use to run each individual migration
     * @param PublisherInterface $publisher
     */
    public function __construct(
        CollectionInterface $collection,
        RunnerInterface $migrationRunner = null,
        PublisherInterface $publisher = null
    ) {
        $this->collection = $collection;

        if (null === $migrationRunner) {
            $migrationRunner = new MigrationRunner(null, $publisher);
        }
        $this->migrationRunner = $migrationRunner;

        parent::__construct($publisher);
    }

    /**
     * Runs a collection of versions towards the specified goal and using the specified options
     *
     * @param VersionInterface $target
     * @param OptionsInterface $options
     *
     * @return CollectionInterface
     */
    public function run(VersionInterface $target, OptionsInterface $options)
    {
        $current = 1;
        $collection = $this->collection;
        $context = CollectionContext::createWithProgress(max($collection->count(), 1), $current);
        $this->migrationRunner->setContext($context);

        $this->getPublisher()->publish(new CollectionBeforeEvent($target, $options, $collection));

        $modified = new Collection();
        $comparator = $collection->getComparator();

        // IMPROVE: add tests to see if rewind is necessary
        $collection->first(); // rewind
        foreach ($collection as $version) {
            $context->getProgress()->update($current);
            $result = $this->migrationRunner->run($version, $options);
            if ($result) {
                $modified->add($version);
            }
            if ($comparator->compare($version, $target) >= 0) {
                break;
            }
            $current += 1;
        }

        $this->getPublisher()->publish(new CollectionAfterEvent($target, $options, $collection));

        $this->migrationRunner->clearContext();

        return $modified;
    }
}
