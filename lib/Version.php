<?php

namespace Baleen;
use Baleen\Migration\MigrationInterface;
use Baleen\Version\VersionInterface;

/**
 * @{inheritDoc}
 */
class Version implements VersionInterface
{

    /**
     * @var string
     */
    protected $id;

    /**
     * @var bool
     */
    protected $migrated;

    /**
     * @var MigrationInterface
     */
    protected $migration;

    /**
     * Constructor
     *
     * @param $id string
     */
    public function __construct($id)
    {
        $this->id = (string) $id;
    }

    /**
     * @{inheritDoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @{inheritDoc}
     */
    public function isMigrated()
    {
        return $this->migrated;
    }

    /**
     * @{inheritDoc}
     */
    public function setMigrated($migrated)
    {
        $this->migrated = (bool) $migrated;
    }

    /**
     * @{inheritDoc}
     */
    public function setMigration(MigrationInterface $migration)
    {
        $this->migration = $migration;
    }

    /**
     * Returns the migration associated with this version.
     * @return null|MigrationInterface
     */
    public function getMigration()
    {
        return $this->migration;
    }
}
