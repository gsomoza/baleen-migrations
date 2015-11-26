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

namespace Baleen\Migrations\Service\Command\Migrate\Collection;

use Baleen\Migrations\Service\Command\Migrate\AbstractFactoryHandler;
use Baleen\Migrations\Service\Runner\Factory\CollectionRunnerFactoryInterface;
use Baleen\Migrations\Service\Runner\Factory\CreatesCollectionRunnerTrait;
use Baleen\Migrations\Version\Collection\Collection;
use Baleen\Migrations\Version\VersionInterface;

/**
 * Class CollectionHandler
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
final class CollectionHandler
{
    use CreatesCollectionRunnerTrait;

    /**
     * CollectionHandler constructor.
     * @param CollectionRunnerFactoryInterface $factory
     */
    public function __construct(CollectionRunnerFactoryInterface $factory)
    {
        $this->setFactory($factory);
    }


    /**
     * Handle an "up" migration against a collection
     *
     * @param CollectionCommand $command
     *
     * @return bool The result of saving the collection of updated versions to the repository
     */
    public function handle(CollectionCommand $command)
    {
        $target = $command->getTarget();
        $collection = $command->getCollection();
        $options = $command->getOptions();
        $direction = $options->getDirection();
        $comparator = $collection->getComparator();
        if ($direction->isDown()) {
            $comparator = $comparator->getReverse();
        }

        // filter to only get versions that need to be migrated
        $filter = function (VersionInterface $v) use ($target, $comparator, $direction) {
            return ($direction->isUp() ^ $v->isMigrated()) // direction must be opposite to migration status
                    && $comparator->compare($v, $target) <= 0; // version must be before or be equal to target (not
                                                               // affected by direction because comparator is reversed)
        };
        $scheduled = $collection->filter($filter)->sort($comparator);
        // we don't check if $scheduled is empty after filtering because the collection-before and -after events should
        // still be triggered by the runner

        /** @var Collection $changed */
        $changed = $this->createRunnerFor($scheduled)->run($target, $options);

        return $command->getVersionRepository()->updateAll($changed);
    }
}
