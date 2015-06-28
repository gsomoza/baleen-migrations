<?php

namespace Corellian\Version;

use Corellian\Exception\CorellianException;
use Corellian\Exception\InvalidArgumentException;
use Corellian\Version;

class VersionCollection {

    /** @var array */
    private $versions = [];

    public function __construct(array $versions = [])
    {
        $input = [];
        foreach($versions as $version) {
            if (!$version instanceof VersionInterface) {
                throw new InvalidArgumentException('The "versionsArray" parameter must be an array of VerisonInterface objects.');
            }
            $this->addVersion($version);
        }
        $this->versions = $input;
    }

    public function hasVersion(Version $index)
    {
        return isset($this->versions[$index->getId()]);
    }

    /**
     * @param mixed $index
     * @return Version
     */
    public function getVersion($index)
    {
        return $this->versions[$index->getId()];
    }

    /**
     * @param Version $version
     * @throws CorellianException
     */
    public function addVersion(Version $version, $replace = false)
    {
        if (!$replace && $this->hasVersion($version)) {
            throw new CorellianException(
                sprintf('Version with id "%s" already exists in collection', $version->getId())
            );
        }
        $this->versions[$version->getId()] = $version;
    }

    public function removeVersion(Version $version)
    {
        unset($this->versions[$version->getId()]);
    }
}
