<?php

namespace Corellian\Storage;

/**
 * 
 */
interface StorageInterface {

    /**
     * @return \Corellian\VersionInterface
     */
    public function getFinishedVersions();

}
