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

namespace Baleen\Migrations\Event\Timeline;

use Baleen\Migrations\Event\EventInterface;
use Baleen\Migrations\Migration\MigrateOptions;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version\Collection;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class CollectionEvent.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class CollectionEvent extends MigrationEvent implements EventInterface
{
    /**
     * @var Collection
     */
    protected $versions;

    /**
     * CollectionEvent constructor.
     *
     * @param Version        $targetVersion
     * @param MigrateOptions $options
     * @param Collection     $versions
     */
    public function __construct(Version $targetVersion, MigrateOptions $options, Collection $versions)
    {
        parent::__construct($targetVersion, $options);
        $this->versions = $versions;
    }

    /**
     * @return Collection
     */
    public function getVersionCollection()
    {
        return $this->versions;
    }

    /**
     * @return Version
     */
    public function getTargetVersion()
    {
        return $this->version;
    }
}
