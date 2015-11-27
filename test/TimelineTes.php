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
 * <http://www.doctrine-project.org>.
 */

namespace BaleenTest\Migrations;

use Baleen\Migrations\Migration\Options;
use Baleen\Migrations\Migration\Options\Direction;
use Baleen\Migrations\Service\DomainBus\Migrate\Collection\CollectionCommand;
use Baleen\Migrations\Timeline;
use Baleen\Migrations\Timeline\TimelineInterface;
use Baleen\Migrations\Version\Collection\Collection;
use Baleen\Migrations\Version\VersionInterface;
use League\Tactician\CommandBus;
use Mockery as m;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 * IMPROVE: re-implement these test cases as integration tests
 */
class OldTimelineTestCases extends BaseTestCase
{
    /**
     * @param $versions
     * @param $goal
     *
     * @dataProvider versionsAndGoalsProvider
     */
    public function testUpTowards($versions, $goal)
    {
        $instance = $this->getInstance($versions);
        $collection = $instance->getVersions();
        $comparator = $collection->getComparator();
        if (!is_object($goal) || !$goal instanceof VersionInterface) {
            $goal = $collection->find($goal);
        }
        $changed = $instance->upTowards($goal);

        list($before, ) = $collection->partition(function ($i, VersionInterface $v) use ($comparator, $goal) {
            return $comparator->compare($v, $goal) <= 0;
        });
        /** @var \Baleen\Migrations\Shared\Collection\CollectionInterface $before */

        foreach ($before as $version) {
            /** @var VersionInterface $version */
            $this->assertTrue($version->isMigrated(), sprintf('Expected version %s to be migrated', $version->getId()));
        }

        // assert subset doesn't work because they're not the same instances, so we're doing it manually
        foreach ($changed as $version) {
            $this->assertTrue($before->contains(
                $version->getId()),
                sprintf('Version %s is not after goal %s', $version->getId(), $goal->getId())
            );
        }
    }

    /**
     * testDownTowardsWithOptionsForcesDownDirection
     * @return void
     */
    public function testUpTowardsWithOptionsForcesUpDirection()
    {
        $v = $this->buildVersion(1, false);
        /** @var CommandBus|m\Mock $bus */
        $bus = m::mock(CommandBus::class);
        $instance = $this->getInstance([$v], $bus);

        $options = new Options(Direction::down());

        $bus->shouldReceive('handle')->with(m::on(function (CollectionCommand $command) {
            return $command->getOptions()->getDirection()->isUp();
        }))->once();

        $instance->upTowards($v, $options);
    }

    /**
     * @param $versions
     * @param $goal
     *
     * @internal param $collection
     * @dataProvider versionsAndGoalsProvider
     */
    public function testDownTowards($versions, $goal)
    {
        $instance = $this->getInstance($versions);
        $collection = $instance->getVersions();
        $comparator = $collection->getComparator();
        if (!is_object($goal) || !$goal instanceof VersionInterface) {
            $goal = $collection->find($goal);
        }
        $changed = $instance->downTowards($goal);

        /** @var Collection $after */
        list(, $after) = $collection->partition(function ($i, VersionInterface $v) use ($comparator, $goal) {
            return $comparator($v, $goal) < 0; // less than goal, cause goal is included in the downTowards run
        });

        foreach ($after as $version) {
            /** @var VersionInterface $version */
            $this->assertFalse($version->isMigrated(), sprintf('Expected version %s not to be migrated', $version->getId()));
        }

        // assert subset doesn't work because they're not the same instances, so we're doing it manually
        foreach ($changed as $version) {
            $this->assertTrue($after->has(
                $version->getId()),
                sprintf('Version %s is not after goal %s', $version->getId(), $goal->getId())
            );
        }
    }

    /**
     * testDownTowardsWithOptionsForcesDownDirection
     * @return void
     */
    public function testDownTowardsWithOptionsForcesDownDirection()
    {
        $v = $this->buildVersion(1, true);
        /** @var CommandBus|m\Mock $bus */
        $bus = m::mock(CommandBus::class);
        $instance = $this->getInstance([$v], $bus);

        $options = new Options(Direction::up());

        $bus->shouldReceive('handle')->with(m::on(function (CollectionCommand $command) {
            return $command->getOptions()->getDirection()->isDown();
        }))->once();

        $instance->downTowards($v, $options);
    }

    /**
     * @param $versions
     * @param $goal
     *
     * @dataProvider versionsAndGoalsProvider
     */
    public function testGoTowards($versions, $goal)
    {
        $instance = $this->getInstance($versions);
        if (!is_object($goal) || !$goal instanceof VersionInterface) {
            $goal = $instance->getVersions()->find($goal);
        }
        $changed = $instance->goTowards($goal);

        $collection = $instance->getVersions();
        $comparator = $collection->getComparator();

        /** @var Collection $before */
        /** @var Collection $after */
        list($before, $after) = $changed->partition(function ($index, VersionInterface $v) use ($comparator, $goal) {
            return $comparator($v, $goal) <= 0;
        });

        foreach ($before as $version) {
            /** @var VersionInterface $version */
            $this->assertTrue($version->isMigrated(), sprintf('Expected version %s to be migrated', $version->getId()));
        }

        foreach ($after as $version) {
            /** @var VersionInterface $version */
            $this->assertFalse($version->isMigrated(), sprintf('Expected version %s not to be migrated', $version->getId()));
        }
    }



    /**
     * testGetLastMigratedVersion
     */
    public function testGetVersions()
    {
        $versions = $this->buildVersions(range(1, 3), false);

        $instance = $this->getInstance($versions);
        $result = $instance->getVersions();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(count($versions), $result);
        foreach ($versions as $v) {
            $this->assertSame($v, $result->get($v->getId()));
        }
    }
}
