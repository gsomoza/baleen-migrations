<?php

namespace BaleenTest\Migrations\CustomRegex;

use Baleen\Migration\MigrationInterface;
use Baleen\Migration\RunOptions;

/**
 * Use the following regex to load this class with the DirectoryRepository: /Version_([0-9]+).*?/
 */
class Version_201507020433_CustomRegex implements MigrationInterface
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
