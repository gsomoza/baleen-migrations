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

use Baleen\Migrations\Exception\MigrationMissingException;
use Baleen\Migrations\Timeline;
use Baleen\Migrations\Version\Collection;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class TimelineFactory
{
    /** @var Collection */
    private $availableVersions;

    /** @var Collection */
    private $migratedVersions;

    /**
     * @param array $availableVersions
     * @param array $migratedVersions
     */
    public function __construct($availableVersions, $migratedVersions = [])
    {
        if (is_array($availableVersions)) {
            $availableVersions = new Collection($availableVersions);
        }
        if (is_array($migratedVersions)) {
            $migratedVersions = new Collection($migratedVersions);
        }
        $this->availableVersions = $availableVersions;
        $this->migratedVersions = $migratedVersions;
    }

    /**
     * Creates a Timeline instance with all available versions. Those versions that have already been migrated will
     * be marked accordingly.
     *
     * @param callable $comparator
     * @param bool     $useInternalDispatcher Whether to create an internal event dispatcher.
     *
     * @return Timeline
     *
     * @throws MigrationMissingException
     */
    public function create(callable $comparator = null, $useInternalDispatcher = true)
    {
        foreach ($this->migratedVersions as $version) {
            if ($this->availableVersions->has($version)) {
                $availableVersion = $this->availableVersions->get($version);
                $availableVersion->setMigrated(true);
            } else {
                throw new MigrationMissingException(
                    sprintf(
                        'Version "%s" is reported as migrated but a corresponding migration could not be found.',
                        $version->getId()
                    )
                );
            }
        }

        $timeline = new Timeline($this->availableVersions, $comparator);
        if ($useInternalDispatcher) {
            $timeline->setEventDispatcher(new EventDispatcher());
        }

        return $timeline;
    }
}
