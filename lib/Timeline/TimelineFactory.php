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
use Baleen\Migrations\Version\Collection\Linked;
use Baleen\Migrations\Version\Collection\Migrated;
use Baleen\Migrations\Version\Collection\Resolver\ResolverInterface;
use Baleen\Migrations\Version\Collection\Sortable;
use Baleen\Migrations\Version\Comparator\ComparatorInterface;
use Baleen\Migrations\Version\VersionInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class TimelineFactory
{
    /**
     * @var \Baleen\Migrations\Version\Collection\Resolver\ResolverInterface
     */
    private $resolver;

    /**
     * @var ComparatorInterface
     */
    private $comparator;
    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    /**
     * @param \Baleen\Migrations\Version\Collection\Resolver\ResolverInterface $resolver
     * @param ComparatorInterface $comparator
     * @param EventDispatcher $dispatcher
     */
    public function __construct(
        ResolverInterface $resolver = null,
        ComparatorInterface $comparator = null,
        EventDispatcher $dispatcher = null
    ) {
        $this->resolver = $resolver;
        $this->comparator = $comparator;
        if (null === $dispatcher) {
            $dispatcher = new EventDispatcher();
        }
        $this->dispatcher = $dispatcher;
    }

    /**
     * Creates a Timeline instance with all available versions. Those versions that have already been migrated will
     * be marked accordingly.
     *
     * @param array|\Baleen\Migrations\Version\Collection\Linked $available
     * @param array|Migrated $migrated
     * @return Timeline
     * @throws MigrationMissingException
     */
    public function create($available, $migrated = [])
    {
        $collection = $this->prepareCollection($available, $migrated);
        $timeline = new Timeline($collection);
        $timeline->setEventDispatcher($this->dispatcher);

        return $timeline;
    }

    /**
     * Sets versions in $this->availableVersions to migrated if they appear in $this->migratedVersions.
     *
     * @param array|Linked $available
     * @param array|Migrated $migrated
     *
     * @return Linked
     *
     * @throws MigrationMissingException
     */
    protected function prepareCollection($available, $migrated = [])
    {
        if (is_array($available)) {
            $available = new Linked($available, $this->resolver, $this->comparator);
        }
        if (is_array($migrated)) {
            $migrated = new Sortable($migrated, $this->resolver, $this->comparator);
        }

        foreach ($migrated as $version) {
            $availableVersion = $available->getById($version->getId());
            if ($availableVersion) {
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

        return $available;
    }
}
