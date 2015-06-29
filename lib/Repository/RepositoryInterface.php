<?php

namespace Baleen\Repository;

/**
 * In charge of loading Migration files and instantiating them.
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
interface RepositoryInterface {

    /**
     * Returns all migrations available to the repository
     * @return array Array of MigrationInterface
     */
    public function fetchAll();

}
