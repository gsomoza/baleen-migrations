<?php

namespace Corellian\Timeline;
use Corellian\Version\Comparator\DefaultComparator;
use Corellian\Version\VersionCollection;

/**
 * @author Gabriel Somoza
 */
class Timeline {

    /** @var array */
    private $versions;

    /** @var callable */
    private $comparator;

    /**
     * @param VersionCollection $versions
     * @param callable $comparator
     */
    public function __construct(VersionCollection $versions, callable $comparator = null) {
        if (null === $comparator) {
            $comparator = new DefaultComparator();
        }
        $this->comparator = $comparator;
        $this->versions = $versions;
        $this->reOrder();
    }

    /**
     *
     */
    protected function reOrder()
    {
        return usort($this->versions, $this->comparator);
    }

    /**
     * @param \\VersionInterface $version
     */
    public function hasVersion($version) {
        // TODO implement here
    }

    /**
     * @return array<VersionInterface>
     */
    public function getPendingVersions() {
        // TODO implement here
        return null;
    }

}
