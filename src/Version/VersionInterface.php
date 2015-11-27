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

namespace Baleen\Migrations\Version;

use Baleen\Migrations\Migration\Command\MigrateCommand;
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Migration\OptionsInterface;
use Baleen\Migrations\Shared\EntityInterface;

/**
 * Holds meta information about a migration, especially that which is related to its status (i.e. anything that can't
 * be stored in the migration itself).
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
interface VersionInterface extends EntityInterface
{
    /**
     * Returns the ID value object for this version.
     *
     * @return VersionId
     */
    public function getId();

    /**
     * Returns whether the version has been migrated or not. Returns NULL if unknown.
     *
     * @return null|bool
     */
    public function isMigrated();

    /**
     * Returns the entity's ID as string
     *
     * @return string
     */
    public function __toString();

    /**
     * Executes a MigrateCommand through the migrationBus
     *
     * @param OptionsInterface $options
     *
     * @return void
     */
    public function migrate(OptionsInterface $options);

    /**
     * Returns the FQCN of the Migration. Used for sorting.
     *
     * @return string
     */
    public function getMigrationClassName();

    /**
     * The filename of the file in which the migration class has been defined. If the class is defined in a PHP
     * extension, FALSE is returned.
     *
     * @return string|false
     */
    public function getMigrationFileName();
}
