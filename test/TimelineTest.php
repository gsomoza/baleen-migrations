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

use Baleen\Migrations\Event\EventInterface;
use Baleen\Migrations\Event\Timeline\CollectionEvent;
use Baleen\Migrations\Event\Timeline\MigrationEvent;
use Baleen\Migrations\Event\Timeline\Progress;
use Baleen\Migrations\Exception\TimelineException;
use Baleen\Migrations\Migration\Command\MigrateCommand;
use Baleen\Migrations\Migration\Command\MigrationBus;
use Baleen\Migrations\Migration\Command\MigrationBusInterface;
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Migration\Options;
use Baleen\Migrations\Migration\OptionsInterface;
use Baleen\Migrations\Timeline;
use Baleen\Migrations\Timeline\TimelineInterface;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version as V;
use Baleen\Migrations\Version\Collection\Linked;
use Baleen\Migrations\Version\Comparator\IdComparator;
use Baleen\Migrations\Version\LinkedVersion;
use Baleen\Migrations\Version\VersionInterface;
use Mockery as m;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class TimelineTest extends BaseTestCase
{

    /**
     * @var \Mockery\Mock
     */
    protected $migrationMock;

    /**
     * @param array $versions
     * @return Timeline|m\Mock
     */
    public function getInstance($versions = [])
    {
        $linked = (new Linked($versions, null, new IdComparator()))->sort();
        $timeline = new Timeline($linked);
        return m::mock($timeline)->shouldAllowMockingProtectedMethods();
    }

    /**
     * testConstructor
     */
    public function testConstructor()
    {
        /** @var Linked|m\Mock $collection */
        $collection = m::mock(Linked::class)->makePartial();
        $this->assertInstanceOf(TimelineInterface::class, new Timeline($collection));
    }

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
            $goal = $collection->get($goal);
        }
        $changed = $instance->upTowards($goal);

        /** @var Linked $before */
        list($before, ) = $collection->partition(function ($i, VersionInterface $v) use ($comparator, $goal) {
            return $comparator($v, $goal) <= 0;
        });

        foreach ($before as $version) {
            $this->assertTrue($version->isMigrated(), sprintf('Expected version %s to be migrated', $version->getId()));
        }

        // assert subset doesn't work because they're not the same instances, so we're doing it manually
        foreach ($changed as $version) {
            $this->assertTrue($before->has(
                $version->getId()),
                sprintf('Version %s is not after goal %s', $version->getId(), $goal->getId())
            );
        }
    }

    /**
     * testDownTowardsWithOptions
     */
    public function testUpTowardsWithOptionsForcesDownDirection()
    {
        /** @var MigrationInterface|m\Mock $migration */
        $migration = m::mock(MigrationInterface::class);
        $v = new LinkedVersion('v1', true, $migration);
        $instance = $this->getInstance([$v]);

        /** @var OptionsInterface|m\Mock $options */
        $options = m::mock(OptionsInterface::class);
        $options->shouldReceive('withDirection')->once()->with(OptionsInterface::DIRECTION_UP)->andReturnSelf();
        $options->shouldReceive(['isForced' => false, 'isDirectionUp' => true, 'isExceptionOnSkip' => false]);

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
            $goal = $collection->get($goal);
        }
        $changed = $instance->downTowards($goal);

        /** @var Linked $after */
        list(, $after) = $collection->partition(function ($i, VersionInterface $v) use ($comparator, $goal) {
            return $comparator($v, $goal) < 0; // less than goal, cause goal is included in the downTowards run
        });

        foreach ($after as $version) {
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
     * testDownTowardsWithOptions
     */
    public function testDownTowardsWithOptionsForcesDownDirection()
    {
        /** @var MigrationInterface|m\Mock $migration */
        $migration = m::mock(MigrationInterface::class);
        $v = new LinkedVersion('v1', false, $migration);
        $instance = $this->getInstance([$v]);

        /** @var OptionsInterface|m\Mock $options */
        $options = m::mock(OptionsInterface::class);
        $options->shouldReceive('withDirection')->once()->with(OptionsInterface::DIRECTION_DOWN)->andReturnSelf();
        $options->shouldReceive(['isForced' => false, 'isDirectionUp' => false, 'isExceptionOnSkip' => false]);

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
            $goal = $instance->getVersions()->get($goal);
        }
        $changed = $instance->goTowards($goal);

        $collection = $instance->getVersions();
        $comparator = $collection->getComparator();

        /** @var Linked $before */
        /** @var Linked $after */
        list($before, $after) = $changed->partition(function ($index, VersionInterface $v) use ($comparator, $goal) {
            return $comparator($v, $goal) <= 0;
        });

        foreach ($before as $version) {
            $this->assertTrue($version->isMigrated(), sprintf('Expected version %s to be migrated', $version->getId()));
        }

        foreach ($after as $version) {
            $this->assertFalse($version->isMigrated(), sprintf('Expected version %s not to be migrated', $version->getId()));
        }
    }

    /**
     * getAllMigratedVersionsFixture
     * @return V[]
     */
    public function getAllMigratedVersionsFixture()
    {
        return $this->getFixtureFor([
            ['id' => 'v01', 'migrated' => true],
            ['id' => 'v02', 'migrated' => true],
            ['id' => 'v03', 'migrated' => true],
            ['id' => 'v04', 'migrated' => true],
            ['id' => 'v05', 'migrated' => true],
            ['id' => 'v06', 'migrated' => true],
            ['id' => 'v07', 'migrated' => true],
            ['id' => 'v08', 'migrated' => true],
            ['id' => 'v09', 'migrated' => true],
            ['id' => 'v10', 'migrated' => true],
            ['id' => 'v11', 'migrated' => true],
            ['id' => 'v12', 'migrated' => true],
        ]);
    }

    /**
     * getNoMigratedVersionsFixture
     * @return V[]
     */
    public function getNoMigratedVersionsFixture()
    {
        return $this->getFixtureFor([
            ['id' => 'v01', 'migrated' => false],
            ['id' => 'v02', 'migrated' => false],
            ['id' => 'v03', 'migrated' => false],
            ['id' => 'v04', 'migrated' => false],
            ['id' => 'v05', 'migrated' => false],
            ['id' => 'v06', 'migrated' => false],
            ['id' => 'v07', 'migrated' => false],
            ['id' => 'v08', 'migrated' => false],
            ['id' => 'v09', 'migrated' => false],
            ['id' => 'v10', 'migrated' => false],
            ['id' => 'v11', 'migrated' => false],
            ['id' => 'v12', 'migrated' => false],
        ]);
    }

    /**
     * getMixedVersionsFixture
     * @return V[]
     */
    public function getMixedVersionsFixture()
    {
        return $this->getFixtureFor([
            ['id' => 'v01', 'migrated' => true],
            ['id' => 'v02', 'migrated' => false],
            ['id' => 'v03', 'migrated' => true],
            ['id' => 'v04', 'migrated' => true],
            ['id' => 'v05', 'migrated' => false],
            ['id' => 'v06', 'migrated' => false],
            ['id' => 'v07', 'migrated' => false],
            ['id' => 'v08', 'migrated' => true],
            ['id' => 'v09', 'migrated' => false],
            ['id' => 'v10', 'migrated' => true],
            ['id' => 'v11', 'migrated' => false],
            ['id' => 'v12', 'migrated' => false],
        ]);
    }

    /**
     * This fixture is meant to cover all use-cases.
     *
     * @param array $versions
     * @return V[]
     */
    public function getFixtureFor(array $versions)
    {
        /** @var MigrationInterface|m\Mock $migrationMock */
        $migrationMock = m::mock(MigrationInterface::class);
        $migrationMock->shouldReceive('up')->zeroOrMoreTimes();
        $migrationMock->shouldReceive('down')->zeroOrMoreTimes();
        $migrationMock->shouldReceive('abort')->zeroOrMoreTimes();
        $migrationMock->shouldReceive('setOptions')->zeroOrMoreTimes();
        $this->migrationMock = $migrationMock;
        return array_map(function ($arr) use ($migrationMock) {
            return new LinkedVersion($arr['id'], $arr['migrated'], clone $migrationMock);
        }, $versions);
    }

    /**
     * versionsAndGoalsProvider
     * @return array
     */
    public function versionsAndGoalsProvider()
    {
        $goals = ['v01', 'v08', 'v12', 'first', 'last'];
        $fixtures = [
            $this->getAllMigratedVersionsFixture(),
            $this->getNoMigratedVersionsFixture(),
            $this->getMixedVersionsFixture(),
        ];
        return $this->combinations([$fixtures, $goals]);
    }

    /**
     * Integration tests to see if Timeline can emmit events
     */
    public function testEmitsMigrationAndCollectionEvents()
    {
        $self = $this;
        $listened = [
            EventInterface::COLLECTION_BEFORE => false,
            EventInterface::COLLECTION_AFTER  => false,
            EventInterface::MIGRATION_BEFORE  => false,
            EventInterface::MIGRATION_AFTER   => false,
        ];
        /** @var MigrationInterface|m\Mock $migration */
        $migration = m::mock(MigrationInterface::class);
        $version = new LinkedVersion('1', false, $migration);
        $migration->shouldReceive('up')->once();
        $options = new Options(Options::DIRECTION_UP);

        $dispatcher = new EventDispatcher();
        $timeline = $this->getInstance([$version]);
        $timeline->setEventDispatcher($dispatcher);

        $this->assertSame($dispatcher, $timeline->getEventDispatcher());

        $collection = $timeline->getVersions();

        $dispatcher->addListener(
            EventInterface::COLLECTION_BEFORE,
            function($event, $name) use ($version, $options, $collection, &$listened, $self) {
                $listened[$name] = true;
                $self->assertInstanceOf(EventInterface::class, $event);
                $self->assertInstanceOf(CollectionEvent::class, $event);
                /** @var CollectionEvent $event */
                $self->assertInstanceOf(Progress::class, $event->getProgress());
                $self->assertEquals($collection->count(), $event->getProgress()->getTotal());
                $self->assertEquals(1, $event->getProgress()->getCurrent());

                $self->assertTrue($options->equals($event->getOptions()));
                $self->assertArraySubset($event->getCollection()->toArray(), $collection->toArray());
                // the following also asserts that the version is NOT migrated
                $self->assertSame($version, $event->getTarget());
            }
        );
        $dispatcher->addListener(
            EventInterface::MIGRATION_BEFORE,
            function($event, $name) use ($version, $options, &$listened, $collection, $self) {
                $listened[$name] = true;
                $self->assertInstanceOf(EventInterface::class, $event);
                $self->assertInstanceOf(MigrationEvent::class, $event);
                /** @var MigrationEvent $event */
                $self->assertInstanceOf(Progress::class, $event->getProgress());
                $self->assertEquals($collection->count(), $event->getProgress()->getTotal());
                $self->assertEquals(
                    $collection->indexOf($event->getVersion()) + 1,
                    $event->getProgress()->getCurrent()
                );

                $self->assertTrue($options->equals($event->getOptions()));
                // the following also asserts that the version is NOT migrated
                $self->assertSame($version, $event->getVersion());
            }
        );
        $dispatcher->addListener(
            EventInterface::MIGRATION_AFTER,
            function($event, $name) use ($version, $options, &$listened, $collection, $self) {
                $listened[$name] = true;
                $self->assertInstanceOf(EventInterface::class, $event);
                $self->assertInstanceOf(MigrationEvent::class, $event);
                /** @var MigrationEvent $event */
                $self->assertInstanceOf(Progress::class, $event->getProgress());
                $self->assertEquals($collection->count(), $event->getProgress()->getTotal());
                $self->assertEquals(
                    $collection->indexOf($event->getVersion()) + 1,
                    $event->getProgress()->getCurrent()
                );

                $self->assertTrue($options->equals($event->getOptions()));
                $self->assertTrue($event->getVersion()->isMigrated());
            }
        );
        $dispatcher->addListener(
            EventInterface::COLLECTION_AFTER,
            function($event, $name) use ($version, $options, &$listened, $collection, $self) {
                $listened[$name] = true;
                $self->assertInstanceOf(EventInterface::class, $event);
                $self->assertInstanceOf(CollectionEvent::class, $event);
                /** @var CollectionEvent $event */
                $self->assertInstanceOf(Progress::class, $event->getProgress());
                $self->assertEquals($collection->count(), $event->getProgress()->getTotal());
                $self->assertEquals($collection->count(), $event->getProgress()->getCurrent());

                $self->assertTrue($options->equals($event->getOptions()));
                $self->assertTrue($event->getTarget()->isMigrated());
            }
        );

        $timeline->upTowards($version, $options);

        foreach ($listened as $name => $result) {
            $this->assertTrue($result, sprintf('Expected Timeline to dispatch %s.', $name));
        }
    }

    /**
     * @param $id
     * @param OptionsInterface $options
     * @param $expectation
     *
     * @throws TimelineException
     *
     * @dataProvider runSingleProvider
     */
    public function testRunSingle($id, OptionsInterface $options, $expectation)
    {
        $versions = new Linked($this->getMixedVersionsFixture());
        $instance = new Timeline($versions);

        $collection = $instance->getVersions();

        /** @var Version\VersionInterface $version */
        $version = $collection->get($id);
        /** @var MigrationInterface|m\Mock $migration */
        $migration = $version->getMigration();

        if ($expectation == 'exception') {
            $this->setExpectedException(TimelineException::class);
        }

        $result = $instance->runSingle($version, $options);

        if ($expectation == 'skip') {
            $this->assertFalse($result, 'Expected runSingle() to return false when skipping without exception.');
        } elseif ($expectation !== 'exception') {
            $migration->shouldHaveReceived($expectation)->once();
            $this->assertTrue($version->isMigrated() == $options->isDirectionUp());
            $this->assertSame($version, $result);
        }
    }

    /**
     * runSingleProvider
     * @return array
     */
    public function runSingleProvider()
    {
        return [
            ['v01', new Options(Options::DIRECTION_UP)  , 'exception' ], // its already up
            ['v01', new Options(Options::DIRECTION_DOWN), Options::DIRECTION_DOWN],
            ['v02', new Options(Options::DIRECTION_UP)  , Options::DIRECTION_UP],
            ['v02', new Options(Options::DIRECTION_DOWN), 'exception' ], // its already down
            ['v02', new Options(Options::DIRECTION_DOWN, false, false, false), 'skip' ], // skip without exception
        ];
    }

    public function testDoRunUsesMigrationBusToMigrate()
    {
        /** @var MigrationBusInterface|m\Mock $migrationBus */
        $migrationBus = m::mock(MigrationBusInterface::class);
        $migrationBus->shouldReceive('handle')->with(m::type(MigrateCommand::class))->once();

        $collection = new Linked($this->getNoMigratedVersionsFixture());
        $options = new Options(Options::DIRECTION_UP);
        $instance = new Timeline($collection, $migrationBus);

        $method = new \ReflectionMethod($instance, 'doRun');
        $method->setAccessible(true);
        $method->invoke($instance, $collection->current()->getMigration(), $options);
    }

    /**
     * testGetLastMigratedVersion
     */
    public function testGetVersions()
    {
        /** @var MigrationInterface|m\Mock $migration */
        $migration = m::mock(MigrationInterface::class);
        $versions = LinkedVersion::fromArray(range(1, 3), false, $migration);

        $instance = $this->getInstance($versions);
        $result = $instance->getVersions();

        $this->assertInstanceOf(Linked::class, $result);
        $this->assertCount(count($versions), $result);
        foreach ($versions as $v) {
            $this->assertSame($v, $result->get($v->getId()));
        }
    }
}
