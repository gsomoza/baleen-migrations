<?php

namespace Corellian\Storage;
use Corellian\Exception\InvalidArgumentException;
use Corellian\Version;

/**
 * @author Gabriel Somoza
 */
class FileStorage implements StorageInterface {

    private $events;
    private $path;

    /**
     * @param $path
     * @param $events
     * @throws InvalidArgumentException
     */
    public function __construct($path, $events) {
        if (!is_file($path) || !is_writeable($path)) {
            throw new InvalidArgumentException('Argument "path" must be a valid path to a file which must be writable.');
        }
        $this->path = $path;
        $this->events = $events;
    }

    /**
     * @return array Array of VersionInterface instances.
     */
    public function getRunVersions() {
        // TODO implement here
        return [];
    }

    /**
     * @param Version $version
     */
    public function hasRun(Version $version) {
        // TODO implement here
    }

    /**
     * @return \Corellian\VersionInterface
     */
    public function getFinishedVersions()
    {
        // TODO: Implement getFinishedVersions() method.
    }
}
