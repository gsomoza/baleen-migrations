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
     * Indicate whether this version has been migrated or not.
     *
     * @param bool|null $migrated
     *
     * @return static
     */
    public function setMigrated($migrated);

    /**
     * Returns the entity's ID as string
     *
     * @return string
     */
    public function __toString();

    /**
     * Returns a MigrateCommand that can be used to migrate this version using the specified options.
     *
     * @param OptionsInterface $options
     *
     * @return MigrateCommand
     */
    public function getMigrateCommand(OptionsInterface $options);

    /**
     * Returns the FQCN of the MigrationInterface object. Used for sorting with some comparators.
     *
     * @return MigrationInterface
     */
    public function getMigration();
}
