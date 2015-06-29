<?php

namespace Baleen\Storage;
use Baleen\Exception\InvalidArgumentException;
use Baleen\Version;

/**
 * @{inheritdoc}
 */
class FileStorage implements StorageInterface
{

    protected $path;

    /**
     * @param $path
     * @throws InvalidArgumentException
     */
    public function __construct($path) {
        if (!is_file($path) && !is_writeable(realpath(dirname($path)))) {
            throw new InvalidArgumentException('Argument "path" must be a valid path to a file which must be writable.');
        }
        $this->path = $path;
    }

    /**
     * Reads versions from the storage file.
     * @return \Baleen\Version\Collection
     */
    public function readMigratedVersions()
    {
        $contents = explode("\n", file_get_contents($this->path));
        $versions = [];
        foreach($contents as $versionId) {
            $versionId = trim($versionId);
            if (!empty($versionId)) { // skip empty lines
                $version = new Version($versionId);
                $version->setMigrated(true);
                $versions[]= $version;
            }
        }
        return $versions;
    }

    /**
     * Write a collection of versions to the storage file.
     * @param array $versions
     * @return int
     */
    public function writeMigratedVersions(array $versions)
    {
        $ids = [];
        foreach($versions as $version) {
            /** @var Version $version */
            if ($version->isMigrated()) {
                $ids[] = $version->getId();
            }
        }
        $contents = implode("\n", $ids);
        return file_put_contents($this->path, $contents) !== false;
    }
}
