<?php

namespace Corellian\Migration;

/**
 * 
 */
interface MigrationInterface {

    /**
     * 
     */
    public function up();

    /**
     * 
     */
    public function down();

    /**
     * @return \\VersionInterface
     */
    public function getVersion();

}
