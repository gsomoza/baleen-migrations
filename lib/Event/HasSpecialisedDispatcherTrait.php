<?php

namespace Baleen\Migrations\Event;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Trait to be used by classes that fire events through a specialised event dispatcher. For example, the Timeline
 * class will use this trait to fire events using the TimelineDispatcher.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
trait HasSpecialisedDispatcherTrait
{
    /**
     * @var SpecialisedDispatcher
     */
    protected $specialisedDispatcher = null;

    /**
     * Set the EventDispatcher for the specialised dispatcher.
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->getDispatcher()->setEventDispatcher($eventDispatcher);
    }

    /**
     * Get the event dispatcher from the specialised dispatcher.
     *
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->getDispatcher()->getEventDispatcher();
    }

    /**
     * Returns the specialised dispatcher for the class. Creates it if necessary.
     *
     * @return SpecialisedDispatcher
     */
    protected function getDispatcher()
    {
        if (!$this->specialisedDispatcher) {
            $this->specialisedDispatcher = $this->createDefaultDispatcher();
        }
        return $this->specialisedDispatcher;
    }

    /**
     * Sets the specialised dispatcher for the class.
     *
     * @param SpecialisedDispatcher $specialisedDispatcher
     */
    protected function setDispatcher(SpecialisedDispatcher $specialisedDispatcher)
    {
        $this->specialisedDispatcher = $specialisedDispatcher;
    }

    /**
     * Must create and return a default specialised dispatcher
     *
     * @return SpecialisedDispatcher
     */
    abstract protected function createDefaultDispatcher();
}
