<?php

namespace Baleen\Version\Comparator;
use Baleen\Version\VersionInterface;

/**
 * Compares two version with each other.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
interface ComparatorInterface {

    /**
     * Compares two versions with each other. The comparison function must return an integer less than, equal to, or
     * greater than zero if the first argument is considered to be respectively less than, equal to, or greater than the
     * second.
     *
     * @param VersionInterface $version1
     * @param VersionInterface $version2
     * @return int
     */
    public function __invoke(VersionInterface $version1, VersionInterface $version2);

}
