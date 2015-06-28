<?php

namespace Corellian\Version\Comparator;
use Corellian\Version\VersionInterface;

/**
 * 
 */
interface ComparatorInterface {

    public function __invoke(VersionInterface $version1, VersionInterface $version2);

}
