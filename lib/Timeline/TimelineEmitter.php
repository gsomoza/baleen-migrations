<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <https://github.com/baleen/migrations>.
 */

namespace Baleen\Migrations\Timeline;

use Baleen\Migrations\Event\CanDispatchEventsTrait;
use Baleen\Migrations\Event\EmitterInterface;
use Baleen\Migrations\Event\EventInterface;
use Baleen\Migrations\Event\Timeline\CollectionEvent;
use Baleen\Migrations\Event\Timeline\MigrationEvent;
use Baleen\Migrations\Event\Timeline\Progress;
use Baleen\Migrations\Migration\OptionsInterface;
use Baleen\Migrations\Version\Collection\Linked;
use Baleen\Migrations\Version\VersionInterface;

/**
 * Dispatches Timeline events.
 */
final class TimelineEmitter implements EmitterInterface
{
    use CanDispatchEventsTrait;

    /**
     * @param VersionInterface $targetVersion
     * @param OptionsInterface $options
     * @param Linked $versions
     * @param Progress $progress
     * @return \Symfony\Component\EventDispatcher\Event|void
     */
    public function dispatchCollectionBefore(
        VersionInterface $targetVersion,
        OptionsInterface $options,
        Linked $versions,
        Progress $progress = null
    ) {
        $event = new CollectionEvent($targetVersion, $options, $versions, $progress);
        return $this->dispatchEvent(EventInterface::COLLECTION_BEFORE, $event);
    }

    /**
     * dispatchCollectionAfter.
     *
     * @param VersionInterface $targetVersion
     * @param OptionsInterface $options
     * @param Linked $versions
     * @param Progress $progress
     * @return \Symfony\Component\EventDispatcher\Event|void
     */
    public function dispatchCollectionAfter(
        VersionInterface $targetVersion,
        OptionsInterface $options,
        Linked $versions,
        Progress $progress = null
    ) {
        $event = new CollectionEvent($targetVersion, $options, $versions, $progress);
        return $this->dispatchEvent(EventInterface::COLLECTION_AFTER, $event);
    }

    /**
     * dispatchMigrationBefore.
     *
     * @param VersionInterface $version
     * @param OptionsInterface $options
     * @param Progress|null $progress
     * @return \Symfony\Component\EventDispatcher\Event|void
     */
    public function dispatchMigrationBefore(
        VersionInterface $version,
        OptionsInterface $options,
        Progress $progress = null
    ) {
        $event = new MigrationEvent($version, $options, $progress);
        return $this->dispatchEvent(EventInterface::MIGRATION_BEFORE, $event);
    }

    /**
     * dispatchMigrationAfter.
     *
     * @param VersionInterface $version
     * @param OptionsInterface $options
     * @param Progress|null $progress
     * @return \Symfony\Component\EventDispatcher\Event|void
     */
    public function dispatchMigrationAfter(
        VersionInterface $version,
        OptionsInterface $options,
        Progress $progress = null
    ) {
        $event = new MigrationEvent($version, $options, $progress);
        return $this->dispatchEvent(EventInterface::MIGRATION_AFTER, $event);
    }
}
