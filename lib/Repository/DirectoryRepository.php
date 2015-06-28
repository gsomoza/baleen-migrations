<?php

namespace Corellian\Repository;
use Corellian\Exception\InvalidArgumentException;
use Corellian\VersionInterface;

/**
 * @author Gabriel Somoza
 */
class DirectoryRepository implements RepositoryInterface {

    /**
     * @var string
     */
    private $path;

    /**
     *
     */
    public function __construct($path) {
        if (empty($path) || !is_dir($path)) {
            throw new InvalidArgumentException('Argument "path" is empty or directory does not exist.');
        }
    }


    /**
     * Returns the migration that corresponds to a given version
     * @param \Corellian\VersionInterface $version
     * @return \Corellian\Migration\MigrationInterface
     */
    public function fetchOneByVersion(VersionInterface $version)
    {
        // TODO: Implement fetchOneByVersion() method.
    }

    /**
     * Returns all migrations available to the repository
     * @return array Array of MigrationInterface instances
     */
    public function fetchAll()
    {
        // TODO: Implement fetchAll() method.
    }
}
