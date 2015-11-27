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

namespace Baleen\Migrations\Service\DomainBus\Migrate\Converge;

use Baleen\Migrations\Migration\Options\Direction;
use Baleen\Migrations\Service\DomainBus\Migrate\AbstractRunnerHandler;
use Baleen\Migrations\Service\DomainBus\Migrate\Collection\CollectionCommand;

/**
 * Class ConvergeHandler
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
final class ConvergeHandler
{
    /**
     * converge (v): come together from different directions so as eventually to meet.
     *
     * @param ConvergeCommand $command
     *
     * @return \Baleen\Migrations\Shared\Collection\CollectionInterface
     */
    public function handle(ConvergeCommand $command)
    {
        $collection = $command->getCollection();
        $targetUp = $command->getTarget();
        $options = $command->getOptions();
        $domainBus = $command->getDomainBus();

        $position = $collection->getPosition($targetUp);
        $targetDown = $collection->getByPosition($position + 1);

        $changed = clone $collection;
        $changed->clear();

        $upCommand = new CollectionCommand(
            $collection,
            $targetUp,
            $options->withDirection(Direction::up()),
            $command->getVersionRepository()
        );
        $upChanges = $domainBus->handle($upCommand);
        if (!empty($upChanges)) {
            $changed->merge($upChanges);
        }

        // if we're not yet at the end of the queue (where no migrations can go down)
        if (null !== $targetDown) {
            $downCommand = new CollectionCommand(
                $collection,
                $targetDown,
                $options->withDirection(Direction::down()),
                $command->getVersionRepository()
            );
            $downChanges = $domainBus->handle($downCommand);
            if (!empty($downChanges)) {
                $changed->merge($downChanges);
            }
        }

        return $changed->sort();
    }
}
