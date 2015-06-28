<?php

namespace Corellian;

/**
 * The Timeline is responsible of emitting MigrateCommands based on how the user wants to navigate the timeline (e.g. travel to a specific version). It takes into account the current state.
 */
public interface TimelineInterface {

    /**
     * @param \\VersionInterface $version
     */
    public function goTowards($version);

    /**
     * @param \\VersionInterface $version
     */
    public function runSingle($version);

}