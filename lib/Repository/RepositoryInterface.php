<?php

namespace Corellian\Repository;
use Corellian\VersionInterface;

/**
 * In charge of loading Migration files and instantiating them.
 */
interface RepositoryInterface {

    /**
     * Returns the migration that corresponds to a given version
     * @param VersionInterface $version
     * @return \Corellian\Migration\MigrationInterface
     */
    public function fetchOneByVersion(VersionInterface $version);

    /**
     * Returns all migrations available to the repository
     * @return array Array of MigrationInterface
     */
    public function fetchAll();

}
