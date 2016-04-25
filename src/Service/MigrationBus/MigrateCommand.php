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

namespace Baleen\Migrations\Service\MigrationBus;

use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Migration\OptionsInterface;

/**
 * Class MigrateCommand.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
final class MigrateCommand
{
    /**
     * @var MigrationInterface
     */
    private $migration;

    /**
     * @var OptionsInterface
     */
    private $options;

    /**
     * @param MigrationInterface $migration
     * @param OptionsInterface $options
     */
    public function __construct(MigrationInterface $migration, OptionsInterface $options)
    {
        $this->migration = $migration;
        $this->options = $options;
    }

    /**
     * @return MigrationInterface
     */
    public function getMigration()
    {
        return $this->migration;
    }

    /**
     * @param MigrationInterface $migration
     */
    public function setMigration(MigrationInterface $migration)
    {
        $this->migration = $migration;
    }

    /**
     * @return OptionsInterface
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param OptionsInterface $options
     */
    public function setOptions(OptionsInterface $options)
    {
        $this->options = $options;
    }
}
