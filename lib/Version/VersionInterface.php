<?php

namespace Baleen\Version;
use Baleen\Migration\MigrationInterface;

/**
 * Holds meta information about a migration, especially that which is related to its status (i.e. anything that can't
 * be stored in the migration itself).
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
interface VersionInterface {

    /**
     * Returns the ID of the version.
     *
     * @return string
     */
    public function getId();

    /**
     * Returns whether the version has been migrated or not.
     *
     * @return mixed
     */
    public function isMigrated();

    /**
     * Sets whether the version has already been migrated or not.
     *
     * @param $migrated boolean
     */
    public function setMigrated($migrated);

    /**
     * Sets the migration class this version corresponds to
     * @param MigrationInterface $migration
     * @return mixed
     */
    public function setMigration(MigrationInterface $migration);

    /**
     * Returns the migration associated with this version.
     * @return null|MigrationInterface
     */
    public function getMigration();
}
