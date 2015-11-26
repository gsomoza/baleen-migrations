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

use Baleen\Migrations\Migration\Capabilities\OptionsAwareInterface;
use Baleen\Migrations\Migration\Command\Middleware\AbstractMiddleware;
use Baleen\Migrations\Migration\Command\Middleware\SetOptionsMiddleware;
use Baleen\Migrations\Migration\Command\MigrateCommand;
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Migration\Options;
use BaleenTest\Migrations\BaseTestCase;
use League\Tactician\Middleware;
use Mockery as m;

/**
 * Class SetOptionsMiddlewareTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class SetOptionsMiddlewareTest extends BaseTestCase
{

    /** @var m\Mock|MigrationInterface */
    protected $migration;

    /** @var MigrateCommand */
    protected $command;

    /** @var callable */
    protected $next;

    /**
     * setUp
     * @return void
     */
    public function setUp()
    {
        $this->migration = m::mock(MigrationInterface::class);
        $this->command = new MigrateCommand($this->migration, new Options());
        $this->next = function() {};
    }

    /**
     * testIsAbstractMiddleware
     * @return void
     */
    public function testIsAbstractMiddleware()
    {
        $instance = new SetOptionsMiddleware();
        $this->assertInstanceOf(Middleware::class, $instance);
    }

    /**
     * testExecute
     * @return void
     * @throws \Baleen\Migrations\Exception\InvalidArgumentException
     */
    public function testExecute()
    {
        $nextCalled = false;
        $instance = new SetOptionsMiddleware();
        $this->migration->shouldNotReceive('setOptions');
        $instance->execute($this->command, function() use (&$nextCalled) {
            $nextCalled = true;
        });
        $this->assertTrue($nextCalled, 'expected SetOptionsMiddleware::execute() to call $next().');
    }

    /**
     * testExecuteOptionsAwareMigration
     * @return void
     * @throws \Baleen\Migrations\Exception\InvalidArgumentException
     */
    public function testExecuteOptionsAwareMigration()
    {
        $instance = new SetOptionsMiddleware();
        /** @var MigrationInterface|m\Mock $migration */
        $migration = m::mock(MigrationInterface::class . ', ' . OptionsAwareInterface::class);
        $migration->shouldReceive('setOptions')->with($this->command->getOptions())->once();
        $this->command->setMigration($migration);
        $instance->execute($this->command, function() use (&$nextCalled) {
            $nextCalled = true;
        });
    }
}
