<?php

namespace Baleen\Storage;

/**
 * Provides a collection of Versions that have been migrated.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
interface StorageInterface {

    /**
     * Reads versions from the storage file.
     * @return array
     */
    public function readMigratedVersions();

    /**
     * Write a collection of versions to the storage file.
     * @param array $versions
     * @return bool Returns false on failure.
     */
    public function writeMigratedVersions(array $versions);
}
