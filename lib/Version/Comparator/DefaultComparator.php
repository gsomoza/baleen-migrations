<?php

namespace Baleen\Version\Comparator;
use Baleen\Version\VersionInterface;

/**
 * @{inheritDoc}
 */
class DefaultComparator implements ComparatorInterface
{

    /**
     * @{inheritDoc}
     */
    public function __invoke(VersionInterface $version1, VersionInterface $version2) {
        return (int) $version1->getId() - (int) $version2->getId();
    }

}
