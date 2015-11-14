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

use Baleen\Migrations\Migration\Capabilities\TransactionAwareInterface;
use Baleen\Migrations\Migration\Command\Middleware\AbstractMiddleware;
use Baleen\Migrations\Migration\Command\Middleware\TransactionMiddleware;
use Baleen\Migrations\Migration\Command\MigrateCommand;
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Migration\OptionsInterface;
use BaleenTest\Migrations\BaseTestCase;
use Mockery as m;

/**
 * Class TransactionMiddlewareTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class TransactionMiddlewareTest extends BaseTestCase
{

    /** @var m\Mock|MigrationInterface */
    protected $migration;

    /** @var MigrateCommand */
    protected $command;

    /** @var callable */
    protected $next;

    public function setUp()
    {
        $this->migration = m::mock(MigrationInterface::class);
        /** @var OptionsInterface $options */
        $options = m::mock(OptionsInterface::class);
        $this->command = new MigrateCommand($this->migration, $options);
        $this->next = function() {};
    }

    public function testIsAbstractMiddleware()
    {
        $instance = new TransactionMiddleware();
        $this->assertInstanceOf(AbstractMiddleware::class, $instance);
    }

    public function testExecute()
    {
        $nextCalled = false;
        $instance = new TransactionMiddleware();
        $this->migration->shouldNotReceive('begin', 'finish', 'abort');
        $instance->execute($this->command, function() use (&$nextCalled) {
            $nextCalled = true;
        });
        $this->assertTrue($nextCalled, 'expected SetOptionsMiddleware::doExecute() to call $next().');
    }

    public function testExecuteOptionsAwareMigration()
    {
        $testCases = [true, false];

        foreach ($testCases as $abort) {
            $nextCalled = false;

            $instance = new TransactionMiddleware();
            /** @var MigrationInterface|m\Mock $migration */
            $migration = m::mock(MigrationInterface::class . ', ' . TransactionAwareInterface::class);

            if ($abort) {
                $migration->shouldReceive('begin')->once();
                $migration->shouldReceive('finish')->once()->andThrow(\RuntimeException::class);
                $migration->shouldReceive('abort')->once();
            } else {
                $migration->shouldReceive('begin', 'finish')->once();
                $migration->shouldNotReceive('abort');
            }

            $this->command->setMigration($migration);
            $instance->execute($this->command, function() use (&$nextCalled) {
                $nextCalled = true;
            });

            $this->assertTrue($nextCalled, 'expected TransactionMiddleware::doExecute() to call $next().');
        }
    }
}
