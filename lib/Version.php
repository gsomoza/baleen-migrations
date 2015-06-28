<?php

namespace Corellian;

/**
 * @author Gabriel Somoza
 */
class Version {

    /**
     * 
     */
    public function __construct() {
    }

    /**
     * @var string
     */
    public $id;

    /**
     * @var bool
     */
    public $migrated;

    /**
     * @return string
     */
    public function getId() {
        // TODO implement here
    }

    /**
     * @return bool
     */
    public function isMigrated() {
        // TODO implement here
        return null;
    }

    /**
     * @param bool $migrated
     */
    public function setMigrated($migrated) {
        // TODO implement here
    }

}
