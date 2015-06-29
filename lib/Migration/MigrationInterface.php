<?php

namespace Baleen\Migration;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
interface MigrationInterface {

    public function up();

    public function down();

    public function abort();

    public function setRunOptions(RunOptions $options);

}
