<?php

namespace Baleen\Migrations\Event\Timeline;

use Baleen\Migrations\Event\EventInterface;
use Baleen\Migrations\Migration\MigrateOptions;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version\Collection;
use Symfony\Component\EventDispatcher\Event;

class CollectionEvent extends MigrationEvent implements EventInterface
{
    /**
     * @var Collection
     */
    protected $versions;

    /**
     * CollectionEvent constructor.
     * @param Version $targetVersion
     * @param MigrateOptions $options
     * @param Collection $versions
     */
    public function __construct(Version $targetVersion, MigrateOptions $options, Collection $versions)
    {
        parent::__construct($targetVersion, $options);
        $this->versions = $versions;
    }

    /**
     * @return Collection
     */
    public function getVersionCollection()
    {
        return $this->versions;
    }

    /**
     * @return Version
     */
    public function getTargetVersion()
    {
        return $this->version;
    }
}
