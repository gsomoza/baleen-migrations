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

namespace BaleenTest\Migrations\Service\DomainBus\Migrate\Single;

use Baleen\Migrations\Migration\OptionsInterface;
use Baleen\Migrations\Service\DomainBus\Migrate\AbstractRunnerHandler;
use Baleen\Migrations\Service\DomainBus\Migrate\Single\SingleHandler;
use Baleen\Migrations\Service\Runner\Event\Migration\MigrateAfterEvent;
use Baleen\Migrations\Service\Runner\MigrationRunner;
use Baleen\Migrations\Service\Runner\MigrationRunnerInterface;
use Baleen\Migrations\Service\Runner\RunnerInterface;
use Baleen\Migrations\Common\Event\Context\ContextInterface;
use Baleen\Migrations\Common\Event\DomainEventInterface;
use Baleen\Migrations\Version\Repository\VersionRepositoryInterface;
use Baleen\Migrations\Version\VersionInterface;
use BaleenTest\Migrations\Service\DomainBus\Migrate\HandlerTestCase;
use Mockery as m;

/**
 * Class SingleHandlerTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class SingleHandlerTest extends HandlerTestCase
{
    /**
     * testHandle
     * @return void
     */
    public function testHandle()
    {
        $handler = $this->createHandler();

        /** @var VersionInterface|m\Mock $version */
        $version = m::mock(VersionInterface::class);

        /** @var DomainEventInterface|m\Mock $afterEvent */
        $afterEvent = m::mock(DomainEventInterface::class, [
            'getTarget' => $version,
        ]);

        /** @var RunnerInterface|m\Mock $runner */
        $runner = $this->invokeMethod('getRunner', $handler);
        $runner->shouldReceive('run')
            ->with(
                m::type(VersionInterface::class),
                m::type(OptionsInterface::class)
            )
            ->once()
            ->andReturn($afterEvent);
        $runner->shouldReceive('withContext')
            ->with(m::type(ContextInterface::class))
            ->once()
            ->andReturnSelf();

        $command = SingleCommandTest::createMockedCommand();

        /** @var VersionRepositoryInterface|m\Mock $storage */
        $storage = $command->getVersionRepository();
        $storage->shouldReceive('update')->with($version)->once()->andReturn('foo');

        $result = $handler->handle($command);

        $this->assertEquals('foo', $result);
    }

    /**
     * createHandler
     * @param RunnerInterface $runner
     * @return SingleHandler
     */
    protected function createHandler(RunnerInterface $runner = null) {
        if (null === $runner) {
            /** @var MigrationRunnerInterface|m\Mock $runner */
            $runner = m::mock(MigrationRunnerInterface::class);
        }
        return new SingleHandler($runner);
    }
}
