<?php

namespace Baleen;

use Baleen\Exception\BaleenException;
use Baleen\Exception\MigrationException;
use Baleen\Migration\MigrationInterface;
use Baleen\Migration\RunOptions;
use Baleen\Timeline\TimelineInterface;
use Baleen\Version\Comparator\DefaultComparator;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class Timeline implements TimelineInterface
{

    protected $allowedDirections;

    /** @var array */
    protected $versions;

    /** @var callable */
    protected $comparator;

    /**
     * @param array $versions
     * @param callable $comparator
     */
    public function __construct(array $versions, callable $comparator = null)
    {
        if (null === $comparator) {
            $comparator = new DefaultComparator();
        }
        $this->comparator = $comparator;
        $keyedVersions = [];
        foreach ($versions as $version) {
            /** @var Version $version */
            $keyedVersions[$version->getId()] = $version;
        }
        $this->versions = $keyedVersions;
        $this->reOrder();
    }

    /**
     *
     */
    protected function reOrder()
    {
        return uasort($this->versions, $this->comparator);
    }

    /**
     * @param Version|string $goalVersion
     * @param RunOptions $options
     * @throws \Exception
     */
    public function upTowards($goalVersion, RunOptions $options = null)
    {
        if (null === $options) {
            $options = new RunOptions(RunOptions::DIRECTION_UP);
        }
        $goalVersion = $this->getVersionObject($goalVersion);
        $options->setDirection(RunOptions::DIRECTION_UP); // make sure its right
        foreach ($this->versions as $version) {
            /** @var Version $version */
            if ($options->isForced() || !$version->isMigrated()) {
                $migration = $version->getMigration();
                if (null === $migration) {
                    throw new MigrationException('Migration object missing for registered version "%s".', $version->getId());
                }
                $this->doRun($migration, $options);
                $version->setMigrated(true); // won't get executed if an exception is thrown
            }
            $goalReached = call_user_func($this->comparator, $goalVersion, $version) === 0;
            if ($goalReached) {
                break;
            }
        }
    }

    /**
     * @param Version|string $goalVersion
     * @param RunOptions $options
     * @throws \Exception
     */
    public function downTowards($goalVersion, RunOptions $options = null)
    {
        if (null === $options) {
            $options = new RunOptions(RunOptions::DIRECTION_DOWN);
        }
        $goalVersion = $this->getVersionObject($goalVersion);
        $options->setDirection(RunOptions::DIRECTION_DOWN); // make sure its right
        $goalReached = false;
        end($this->versions);
        while (!$goalReached) {
            $version = current($this->versions);
            /** @var Version $version */
            if ($options->isForced() || $version->isMigrated()) {
                if (null === $version->getMigration()) {
                    throw new MigrationException('Migration object missing for registered version "%s".', $version->getId());
                }
                $this->doRun($version->getMigration(), $options);
                $version->setMigrated(false); // won't get executed if an exception is thrown
            }
            $goalReached = call_user_func($this->comparator, $goalVersion, $version) === 0;
            prev($this->versions);
        }
        reset($this->versions);
    }

    /**
     * Runs migrations up/down so that all versions *before and including* the specified version are "up" and
     * all versions *after* the specified version are "down".
     *
     * @param $goalVersion
     * @param \Baleen\Migration\RunOptions $options
     * @return mixed
     */
    public function goTowards($goalVersion, RunOptions $options = null)
    {
        if (null === $options) {
            $options = new RunOptions(RunOptions::DIRECTION_UP);
        }
        reset($this->versions);
        $this->upTowards($goalVersion, $options);
        // the for-each in upTowards should be pointing to the goalVersion...
        $newGoal = current($this->versions); // ...so make the next item the goal for downTowards
        if ($newGoal !== false) { // are we at the end of the array?
            $this->downTowards($newGoal, $options);
        }
    }

    /**
     * @param \Baleen\Version $version
     * @param RunOptions $options
     * @throws MigrationException
     */
    public function runSingle($version, RunOptions $options)
    {
        switch ($options->getDirection()) {
            case RunOptions::DIRECTION_UP:
                if (!$options->isForced() && $version->isMigrated()) {
                    throw new MigrationException(
                        sprintf("Cowardly refusing to run up() on a version that has already been migrated (%s).", $version->getId())
                    );
                }
                break;

            case RunOptions::DIRECTION_DOWN:
                if (!$options->isForced() && !$version->isMigrated()) {
                    throw new MigrationException(
                        sprintf("Cowardly refusing to run down() on a version that hasn't been migrated yet (%s).", $version->getId())
                    );
                }
            break;
            default:
        }
        $this->doRun($version->getMigration(), $options);
    }

    /**
     * @param MigrationInterface $migration
     * @param RunOptions $options
     * @return bool
     * @throws \Exception
     */
    protected function doRun(MigrationInterface $migration, RunOptions $options) {
        try {
            $direction = $options->getDirection();
            $migration->setRunOptions($options);
            $migration->$direction();
            return true;
        } catch (\Exception $e) {
            $migration->abort();
            throw $e;
        }
    }

    /**
     * @param $version
     * @return mixed A Version object or a string representing the version's ID.
     * @throws BaleenException
     */
    protected function getVersionObject($version)
    {
        if (is_scalar($version)) {
            $version = (string) $version;
            if (!empty($this->versions[$version])) {
                $version = $this->versions[$version];
            } else {
                throw new BaleenException(
                    sprintf('Version "%s" not found in timeline.', $version)
                );
            }
        }
        return $version;
    }
}
