<?php

namespace Baleen\Migrations\Event;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Interface SpecialisedDispatcher
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
interface SpecialisedDispatcher
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
