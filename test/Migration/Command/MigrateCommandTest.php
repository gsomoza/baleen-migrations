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

namespace BaleenTest\Migrations\Migration\Command;

use Baleen\Migrations\Migration\Command\MigrateCommand;
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Migration\OptionsInterface;
use BaleenTest\Migrations\BaseTestCase;
use Mockery as m;

/**
 * Class MigrateCommandTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class MigrateCommandTest extends BaseTestCase
{
    /** @var m\Mock|MigrationInterface */
    protected $migration;

    /** @var m\Mock|OptionsInterface */
    protected $options;

    /**
     * setUp
     * @return void
     */
    public function setUp()
    {
        $this->migration = m::mock(MigrationInterface::class);
        $this->options = m::mock(OptionsInterface::class);
    }

    /**
     * testConstructor
     * @return void
     */
    public function testConstructor()
    {
        $instance = new MigrateCommand($this->migration, $this->options);
        $this->assertSame($this->migration, $instance->getMigration());
        $this->assertSame($this->options, $instance->getOptions());
    }

    /**
     * testSetMigration
     * @return void
     */
    public function testSetMigration()
    {
        /** @var MigrationInterface|m\Mock $migration */
        $migration = m::mock(MigrationInterface::class);
        $this->assertNotSame($this->migration, $migration);

        $instance = new MigrateCommand($this->migration, $this->options);
        $instance->setMigration($migration);
        $this->assertSame($migration, $instance->getMigration());
    }

    /**
     * testSetOptions
     * @return void
     */
    public function testSetOptions()
    {
        /** @var OptionsInterface $options */
        $options = m::mock(OptionsInterface::class);
        $this->assertNotSame($this->options, $options);

        $instance = new MigrateCommand($this->migration, $this->options);
        $instance->setOptions($options);

        $this->assertSame($options, $instance->getOptions());
    }
}
