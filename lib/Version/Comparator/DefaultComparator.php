<?php

namespace Corellian\Version\Comparator;
use Corellian\Version\VersionInterface;

/**
 * @author Gabriel Somoza
 */
class DefaultComparator {

    public function __invoke(VersionInterface $version1, VersionInterface $version2) {
        return (int) $version1->getId() - (int) $version2->getId();
    }

}
