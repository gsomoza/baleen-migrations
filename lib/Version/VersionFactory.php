<?php

namespace Corellian\Version;

use Corellian\Timeline\Timeline;

class VersionFactory {

    /** @var VersionCollection  */
    private $availableVersions;

    /** @var VersionCollection  */
    private $migratedVersions;

    /**
     * @param VersionCollection $availableVersions
     * @param VersionCollection $migratedVersions
     */
    public function __construct(VersionCollection $availableVersions, VersionCollection $migratedVersions)
    {
        $this->availableVersions = $availableVersions;
        $this->migratedVersions = $migratedVersions;
    }

    /**
     * Creates a Timeline instance with all available versions. Those versions that have already been migrated will
     * be marked accordingly.
     *
     * @return Timeline
     */
    public function create()
    {
        foreach($this->migratedVersions as $version) {
            if ($this->availableVersions->hasVersion($version)) {
               $this->availableVersions->getVersion($version)->setMigrated(true);
            }
        }
        return new Timeline($this->availableVersions);
    }

}
