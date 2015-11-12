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

use Baleen\Migrations\Migration\MigrationInterface;

/**
 * Holds meta information about a migration, especially that which is related to its status (i.e. anything that can't
 * be stored in the migration itself).
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
interface VersionInterface
{
    /**
     * Returns the ID of the version. A hash or uuid is recommended.
     *
     * @return string
     */
    public function getId();

    /**
     * Returns whether the version has been migrated or not.
     *
     * @return bool
     */
    public function isMigrated();

    /**
     * Sets whether the version has already been migrated or not.
     *
     * @param $migrated boolean
     */
    public function setMigrated($migrated);

    /**
     * Sets the migration class this version corresponds to.
     *
     * @param MigrationInterface $migration
     *
     * @return void
     */
    public function setMigration(MigrationInterface $migration);

    /**
     * Should return whether the instance has a migration class attached to it or not
     * @return bool
     */
    public function hasMigration();

    /**
     * Returns the migration associated with this version.
     *
     * @return null|MigrationInterface
     */
    public function getMigration();
}
