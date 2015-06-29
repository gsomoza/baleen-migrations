<?php

namespace BaleenTest\Migrations\AllValid;

use Baleen\Migration\MigrationInterface;
use Baleen\Migration\RunOptions;

class v201507020419_AnotherTest implements MigrationInterface
{

    /**
     *
     */
    public function up()
    {
        echo "Do something going UP.";
    }

    /**
     *
     */
    public function down()
    {
        echo "Do something going DOWN.";
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
