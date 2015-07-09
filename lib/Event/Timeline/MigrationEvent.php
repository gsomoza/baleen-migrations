<?php

namespace Baleen\Migrations\Event\Timeline;

use Baleen\Migrations\Event\EventInterface;
use Baleen\Migrations\Migration\MigrateOptions;
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version\Collection;
use Symfony\Component\EventDispatcher\Event;

class MigrationEvent extends Event implements EventInterface
{
    /**
     * @var MigrateOptions
     */
    protected $options;

    /**
     * @var Version
     */
    protected $version;

    /**
     * CollectionEvent constructor.
     * @param Version $version
     * @param MigrateOptions $options
     */
    public function __construct(Version $version, MigrateOptions $options)
    {
        $this->options = $options;
        $this->version = $version;
    }

    /**
     * @return MigrateOptions
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return Version
     */
    public function getVersion()
    {
        return $this->version;
    }
}
