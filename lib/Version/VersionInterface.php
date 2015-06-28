<?php

namespace Corellian\Version;

/**
 * 
 */
interface VersionInterface {

    /**
     * @return int
     */
    public function getId();

    /**
     * @param VersionInterface $version
     * @return
     */
    public function compareWith(VersionInterface $version);

}
