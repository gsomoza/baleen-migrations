<?php

namespace BaleenTest\Migrations\NoneValid;

use Baleen\Migration\MigrationInterface;
use Baleen\Migration\RunOptions;

/**
 * Should not be considered a migration because it doesn't conform to the DirectoryReopsitory's default pattern
 */
class vNonConformantClassName implements MigrationInterface
{
    /**
     *
     */
    public function up()
    {
    }

    /**
     *
     */
    public function down()
    {
    }

    public function abort()
    {
        // TODO: Implement abort() method.
    }

    public function setRunOptions(RunOptions $options)
    {
        // TODO: Implement setRunOptions() method.
    }
}
