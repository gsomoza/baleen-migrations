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

namespace BaleenTest\Migrations\Migration\Command\Middleware;

use Baleen\Migrations\Exception\InvalidArgumentException;
use Baleen\Migrations\Migration\Command\Middleware\AbstractMiddleware;
use Baleen\Migrations\Migration\Command\MigrateCommand;
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Migration\Options;
use BaleenTest\Migrations\BaseTestCase;
use League\Tactician\Middleware;
use Mockery as m;

/**
 * Class AbstractMiddlewareTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class AbstractMiddlewareTest extends BaseTestCase
{
    /**
     * testIsMiddleware
     * @return void
     */
    public function testIsMiddleware()
    {
        /** @var AbstractMiddleware|m\Mock $instance */
        $instance = m::mock(AbstractMiddleware::class)->makePartial();
        $this->assertInstanceOf(Middleware::class, $instance);
    }

    /**
     * testExecuteFailsIfNotMigrationCommand
     * @return void
     * @throws InvalidArgumentException
     */
    public function testExecuteFailsIfNotMigrationCommand()
    {
        /** @var AbstractMiddleware|m\Mock $instance */
        $instance = m::mock(AbstractMiddleware::class)->makePartial();

        $this->setExpectedException(InvalidArgumentException::class);
        $instance->execute(new \stdClass(), function(){});
    }

    /**
     * testExecute
     * @return void
     */
    public function testExecute()
    {
        /** @var AbstractMiddleware|m\Mock $instance */
        $instance = m::mock(AbstractMiddleware::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        /** @var MigrationInterface|m\Mock $migration */
        $migration = m::mock(MigrationInterface::class);
        $command = new MigrateCommand($migration, new Options());
        $next = function() {};

        $instance->shouldReceive('doExecute')->with($command, $next)->once()->andReturn('test');

        $result = $instance->execute($command, $next);

        // test doExecute result is returned as-is
        $this->assertSame('test', $result);
    }
}
