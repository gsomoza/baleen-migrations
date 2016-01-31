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

namespace BaleenTest\Migrations\Service\DomainBus\Migrate\Collection;

use Baleen\Migrations\Migration\Options\Direction;
use Baleen\Migrations\Migration\OptionsInterface;
use Baleen\Migrations\Service\DomainBus\Migrate\Collection\CollectionHandler;
use Baleen\Migrations\Service\Runner\Event\Collection\CollectionAfterEvent;
use Baleen\Migrations\Service\Runner\Factory\CollectionRunnerFactoryInterface;
use Baleen\Migrations\Service\Runner\RunnerInterface;
use Baleen\Migrations\Common\Collection\CollectionInterface;
use Baleen\Migrations\Delta\Comparator\ComparatorInterface;
use Baleen\Migrations\Delta\Repository\VersionRepositoryInterface;
use Baleen\Migrations\Delta\DeltaInterface;
use BaleenTest\Migrations\Service\DomainBus\Migrate\HandlerTestCase;
use Mockery as m;

/**
 * Class CollectionHandlerTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class CollectionHandlerTest extends HandlerTestCase
{
    /**
     * testHandle
     * @return void
     */
    public function testHandle()
    {
        $handler = $this->createHandler();

        $command = CollectionCommandTest::createMockedCommand();

        /** @var OptionsInterface|m\Mock $options */
        $options= $command->getOptions();
        $direction = Direction::down();
        $options->shouldReceive('getDirection')
            ->once()
            ->withNoArgs()
            ->andReturn($direction); // using 'down' because its more declarative for the ->isDown test below

        /** @var CollectionInterface|m\Mock $filteredCollection */
        $filteredCollection = m::mock(CollectionInterface::class);

        /** @var ComparatorInterface|m\Mock $comparator */
        $comparator = m::mock(ComparatorInterface::class);
        $comparator->shouldReceive('compare')
            ->with(m::type(DeltaInterface::class), $command->getTarget())
            ->once()
            ->andReturn(0);
        if ($direction->isDown()) {
            $comparator->shouldReceive('getReverse')->once()->withNoArgs()->andReturnSelf();
        } else {
            $comparator->shouldNotReceive('getReverse');
        }

        /** @var CollectionInterface|m\Mock $collection */
        $collection = $command->getCollection();
        $collection->shouldReceive('getComparator')
            ->once()
            ->andReturn($comparator);
        $collection->shouldReceive('filter')
            ->with(m::on(function (callable $func) {
                /** @var DeltaInterface|m\Mock $v */
                $v = m::mock(DeltaInterface::class);
                $v->shouldReceive('isMigrated')->once()->withNoArgs()->andReturn(true);

                $res = $func($v);
                $this->assertTrue(is_bool($res));
                $this->assertTrue($res);

                return true;
            }))
            ->once()
            ->andReturnSelf();
        $collection->shouldReceive('sort')
            ->once()->with($comparator)->andReturn($filteredCollection);

        /** @var DeltaInterface|m\Mock $target */
        $target = m::mock(DeltaInterface::class);

        /** @var RunnerInterface|m\Mock $runner */
        $runner = $this->invokeMethod('createRunnerFor', $handler, [$collection]);
        $runner->shouldReceive('run')
            ->with(
                m::type(DeltaInterface::class),
                m::type(OptionsInterface::class)
            )
            ->once()
            ->andReturn(new CollectionAfterEvent($target, $options, $filteredCollection));

        /** @var VersionRepositoryInterface|m\Mock $storage */
        $storage = $command->getVersionRepository();
        $storage->shouldReceive('updateAll')->with($filteredCollection)->once()->andReturn('foo');

        $result = $handler->handle($command);

        $this->assertSame('foo', $result);
    }

    /**
     * createHandler
     * @param RunnerInterface $runner
     * @return CollectionHandler
     */
    protected function createHandler(RunnerInterface $runner = null) {
        if (null === $runner) {
            $runner = $this->getRunnerMock();
        }
        /** @var CollectionRunnerFactoryInterface|m\Mock $factory */
        $factory = m::mock(CollectionRunnerFactoryInterface::class);
        $factory->shouldReceive('create')
            ->with(m::type(CollectionInterface::class))
            ->zeroOrMoreTimes()
            ->andReturn($runner);
        return new CollectionHandler($factory);
    }
}
