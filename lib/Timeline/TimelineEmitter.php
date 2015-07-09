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
use Baleen\Migrations\Event\EventInterface;
use Baleen\Migrations\Event\EmitterInterface;
use Baleen\Migrations\Event\Timeline\CollectionEvent;
use Baleen\Migrations\Event\Timeline\MigrationEvent;
use Baleen\Migrations\Migration\MigrateOptions;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version\Collection;

/**
 * Dispatches Timeline events
 *
 * @package Baleen\Migrations\Timeline
 */
class TimelineEmitter implements EmitterInterface
{
    use CanDispatchEventsTrait;

    /**
     * @param Version $targetVersion
     * @param MigrateOptions $options
     * @param Collection $versions
     */
    public function dispatchCollectionBefore(Version $targetVersion, MigrateOptions $options, Collection $versions)
    {
        $event = new CollectionEvent($targetVersion, $options, $versions);
        $this->dispatchEvent(EventInterface::COLLECTION_BEFORE, $event);
    }

    public function dispatchCollectionAfter(Version $targetVersion, MigrateOptions $options, Collection $versions)
    {
        $event = new CollectionEvent($targetVersion, $options, $versions);
        $this->dispatchEvent(EventInterface::COLLECTION_AFTER, $event);
    }

    public function dispatchMigrationBefore(Version $version, MigrateOptions $options)
    {
        $event = new MigrationEvent($version, $options);
        $this->dispatchEvent(EventInterface::MIGRATION_BEFORE, $event);
    }

    public function dispatchMigrationAfter(Version $version, MigrateOptions $options)
    {
        $event = new MigrationEvent($version, $options);
        $this->dispatchEvent(EventInterface::MIGRATION_AFTER, $event);
    }
}
