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

namespace BaleenTest\Migrations\Migration\Command;

use Baleen\Migrations\Migration\Command\Middleware\AbstractMiddleware;
use Baleen\Migrations\Migration\Command\MigrateCommand;
use Baleen\Migrations\Migration\Command\MigrateHandler;
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Migration\Options;
use Baleen\Migrations\Migration\Options\Direction;
use BaleenTest\Migrations\BaseTestCase;
use League\Tactician\Middleware;
use Mockery as m;

/**
 * Class MigrateHandlerTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class MigrateHandlerTest extends BaseTestCase
{
    /**
     * testConstructor
     * @return void
     */
    public function testConstructor()
    {
        $handler = new MigrateHandler();
        $this->assertInstanceOf(Middleware::class, $handler);
    }

    /**
     * testDoExecute
     * @param Direction $direction
     *
     * @dataProvider executeProvider
     *
     * @throws \Baleen\Migrations\Exception\InvalidArgumentException
     */
    public function testExecute(Direction $direction)
    {
        /** @var MigrationInterface|m\Mock $migration */
        $migration = m::mock(MigrationInterface::class);
        $migration->shouldReceive($direction->getDirection())->once()->withNoArgs();

        $command = new MigrateCommand($migration, new Options($direction));
        $handler = new MigrateHandler();
        $called = false;
        $handler->execute($command, function() use (&$called) {
            $called = true;
        });
        $this->assertFalse($called); // we don't want to call $next
    }

    /**
     * executeProvider
     * @return array
     */
    public function executeProvider()
    {
        return [
            [Direction::up()],
            [Direction::down()],
        ];
    }
}
