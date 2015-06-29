<?php

namespace Baleen\Timeline;

use Baleen\Timeline;

class TimelineFactory {

    /** @var array */
    private $availableVersions;

    /** @var array */
    private $migratedVersions;

    /**
     * @param array $availableVersions
     * @param array $migratedVersions
     */
    public function __construct(array $availableVersions, array $migratedVersions)
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
            /** @var \Baleen\Version $version */
            if (!empty($this->availableVersions[$version])) {
                /** @var \Baleen\Version $availableVersion */
                $availableVersion = $this->availableVersions[$version->getId()];
                $availableVersion->setMigrated(true);
            } //TODO: else throw an exception
        }
        return new Timeline($this->availableVersions);
    }

}
