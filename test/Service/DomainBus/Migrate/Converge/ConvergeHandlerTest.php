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

namespace BaleenTest\Migrations\Service\DomainBus\Migrate\Converge;

use Baleen\Migrations\Migration\Options;
use Baleen\Migrations\Service\DomainBus\Migrate\Collection\CollectionCommand;
use Baleen\Migrations\Service\DomainBus\Migrate\Converge\ConvergeCommand;
use Baleen\Migrations\Service\DomainBus\Migrate\Converge\ConvergeHandler;
use Baleen\Migrations\Service\Runner\RunnerInterface;
use Baleen\Migrations\Version\Collection\Collection;
use Baleen\Migrations\Version\Repository\VersionRepositoryInterface;
use BaleenTest\Migrations\BaseTestCase;
use League\Tactician\CommandBus;
use Mockery as m;

/**
 * Class ConvergeHandlerTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class ConvergeHandlerTest extends BaseTestCase
{
    /**
     * testDomainBusIsUsed
     * @param array $upChanges
     * @param array $downChanges
     * @param int|null $finalCount
     *
     * @dataProvider handleProvider
     */
    public function testDomainBusIsUsed($upChanges = [], $downChanges = [], $finalCount = 0)
    {
        /** @var \Baleen\Migrations\Service\Runner\RunnerInterface $runner */
        $runner = m::mock(RunnerInterface::class);
        $handler = new ConvergeHandler($runner);

        $v1 = $this->buildVersion(1);
        $v2 = $this->buildVersion(2);
        $upChanges = new Collection($upChanges);
        $downChanges = new Collection($downChanges);

        $collection = new Collection([$v1, $v2]);

        /** @var CommandBus|m\Mock $domainBus */
        $domainBus = m::mock(CommandBus::class);
        $domainBus->shouldReceive('handle')
            ->with(m::on(function ($arg) use ($v1) {
                return $arg instanceof CollectionCommand
                    && $arg->getOptions()->getDirection()->isUp()
                    && $arg->getTarget()->isSameIdentityAs($v1);
            }))
            ->once()
            ->andReturn($upChanges);

        $domainBus->shouldReceive('handle')
            ->with(m::on(function ($arg) use ($v2) {
                return $arg instanceof CollectionCommand
                    && $arg->getOptions()->getDirection()->isDown()
                    && $arg->getTarget()->isSameIdentityAs($v2);
            }))
            ->once()
            ->andReturn($downChanges);

        /** @var VersionRepositoryInterface|m\Mock $storage */
        $storage = m::mock(VersionRepositoryInterface::class);
        $command = new ConvergeCommand($collection, $v1, new Options(), $domainBus, $storage);

        $changed = $handler->handle($command);

        $this->assertInstanceOf(Collection::class, $changed);
        $this->assertCount($finalCount, $changed);
    }

    /**
     * handleProvider
     * @return array
     */
    public function handleProvider()
    {
        return [
            // no changes
            [[], [], 0],
            // only up changed
            [$this->buildVersions(range(1,3)), [], 3],
            // only down changed
            [[], $this->buildVersions(range(1,4)), 4],
            // intersections
            [$this->buildVersions(range(1,3)), $this->buildVersions(range(1,4)), 4],
            [$this->buildVersions(range(1,4)), $this->buildVersions(range(1,3)), 4],
            [$this->buildVersions(range(1,4)), $this->buildVersions(range(1,4)), 4],
            [$this->buildVersions(range(1,3)), $this->buildVersions(range(3,4)), 4],
            // unions
            [$this->buildVersions(range(1,2)), $this->buildVersions(range(3,5)), 5],
        ];
    }
}
