<?php

namespace Baleen\Migrations\Event\Timeline;

use Baleen\Migrations\Event\EventInterface;
use Baleen\Migrations\Migration\MigrateOptions;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version\Collection;

class MigrationEvent extends \Symfony\Component\EventDispatcher\Event implements EventInterface
{
    /**
     * @var MigrateOptions
     */
    protected $options;

    /**
     * @var Collection
     */
    protected $versions;

    /**
     * @var Version
     */
    protected $goal;

    /**
     * MigrationEvent constructor.
     * @param Version $goal
     * @param MigrateOptions $options
     * @param Collection $versions
     */
    public function __construct(Version $goal, MigrateOptions $options, Collection &$versions)
    {
        $this->options = $options;
        $this->versions = $versions;
    }

    /**
     * @return MigrateOptions
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return Collection
     */
    public function getVersions()
    {
        return $this->versions;
    }

    /**
     * @return Version
     */
    public function getGoal()
    {
        return $this->goal;
    }
}
