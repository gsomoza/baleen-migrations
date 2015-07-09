<?php

namespace Baleen\Migrations\Event;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Trait to be used by classes that fire events through a specialised emitter. For example, the Timeline
 * class will use this trait to fire events using the TimelineEmitter.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
trait HasEmitterTrait
{
    /**
     * @var EmitterInterface
     */
    protected $emitter = null;

    /**
     * Set the EventDispatcher for the emitter. This is public to allow attaching a previously existing EventDispatcher.
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->getEmitter()->setEventDispatcher($eventDispatcher);
    }

    /**
     * Get the event dispatcher from the emitter.
     *
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->getEmitter()->getEventDispatcher();
    }

    /**
     * Returns the emitter for the class. Creates one if necessary.
     *
     * @return EmitterInterface
     */
    protected function getEmitter()
    {
        if (!$this->emitter) {
            $this->emitter = $this->createEmitter();
        }
        return $this->emitter;
    }

    /**
     * Sets the emitter for the class.
     *
     * @param EmitterInterface $emitter
     */
    protected function setEmitter(EmitterInterface $emitter)
    {
        $this->emitter = $emitter;
    }

    /**
     * Must create and return a default emitter
     *
     * @return EmitterInterface
     */
    abstract protected function createEmitter();
}
