<?php

namespace Baleen\Timeline;
use Baleen\Version;
use Baleen\Migration\RunOptions;

/**
 * The Timeline is responsible of emitting MigrateCommands based on how the user wants to navigate the timeline
 * (e.g. travel to a specific version). It takes into account the current state.
 */
interface TimelineInterface {

    /**
     * Runs all versions up, starting from the oldes and until (and including) the specified version.
     *
     * @param string|\Baleen\Version $version
     * @param \Baleen\Migration\RunOptions $options
     * @return void
     */
    public function upTowards($version, RunOptions $options);

    /**
     * Runs all versions down, starting from the newest and until (and including) the specified version.
     *
     * @param string|\Baleen\Version $version
     * @param \Baleen\Migration\RunOptions $options
     * @return void
     */
    public function downTowards($version, RunOptions $options);

    /**
     * Runs migrations up/down so that all versions *before and including* the specified version are "up" and
     * all versions *after* the specified version are "down".
     *
     * @param $goalVersion
     * @param \Baleen\Migration\RunOptions $options
     * @return void
     */
    public function goTowards($goalVersion, RunOptions $options);

    /**
     * @param \Baleen\Version $version
     * @param \Baleen\Migration\RunOptions $options
     * @return
     */
    public function runSingle($version, RunOptions $options);

}
