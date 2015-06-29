<?php

namespace Baleen\Migration;

use Baleen\Exception\InvalidArgumentException;

class RunOptions
{
    const DIRECTION_UP = 'up';
    const DIRECTION_DOWN = 'down';

    /**
     * @var array
     */
    protected $allowedDirections;

    /**
     * @var string
     */
    protected $direction;

    /**
     * @var bool
     */
    protected $forced;

    /**
     * @var bool
     */
    protected $dryRun;

    /**
     * @var array
     */
    protected $custom;

    /**
     * @param $direction
     * @param bool $forced
     * @param bool $dryRun
     * @param array $custom
     * @throws InvalidArgumentException
     */
    function __construct($direction, $forced = false, $dryRun = false, $custom = [])
    {
        $this->allowedDirections = [
            self::DIRECTION_UP,
            self::DIRECTION_DOWN,
        ];
        $this->setDirection($direction);
        $this->forced = (bool) $forced;
        $this->dryRun = (bool) $dryRun;
        $this->custom = $custom;
    }

    /**
     * @return string
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * @param string $direction
     * @throws InvalidArgumentException
     */
    public function setDirection($direction)
    {
        if (!in_array($direction, $this->allowedDirections)) {
            throw new InvalidArgumentException(
                sprintf('Unknown direction "%s". Valid options are "up" or "down".', $direction)
            );
        }
        $this->direction = $direction;
    }

    /**
     * @return boolean
     */
    public function isForced()
    {
        return $this->forced;
    }

    /**
     * @param boolean $forced
     */
    public function setForced($forced)
    {
        $this->forced = (bool) $forced;
    }

    /**
     * @return boolean
     */
    public function isDryRun()
    {
        return $this->dryRun;
    }

    /**
     * @param boolean $dryRun
     */
    public function setDryRun($dryRun)
    {
        $this->dryRun = (bool) $dryRun;
    }

    /**
     * @return array
     */
    public function getCustom()
    {
        return $this->custom;
    }

    /**
     * @param array $custom
     */
    public function setCustom($custom)
    {
        $this->custom = $custom;
    }


}
