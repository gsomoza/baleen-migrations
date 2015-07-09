<?php

namespace Baleen\Migrations\Event;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Interface EmitterInterface
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
interface EmitterInterface
{
    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher);

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher();
}
