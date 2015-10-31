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
use Baleen\Migrations\Exception\MigrationMissingException;
use Baleen\Migrations\Exception\TimelineException;
use Baleen\Migrations\Migration\Command\MigrateCommand;
use Baleen\Migrations\Migration\Command\MigrationBus;
use Baleen\Migrations\Migration\Options;
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Timeline;
use Baleen\Migrations\Version as V;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version\Collection\LinkedVersions;
use Baleen\Migrations\Version\Collection\SortableVersions;
use Baleen\Migrations\Version\Comparator\DefaultComparator;
use Mockery as m;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Zend\Stdlib\Hydrator\ObjectProperty;

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
        return m::mock('Baleen\Migrations\Timeline', [new LinkedVersions($versions)])->makePartial()->shouldAllowMockingProtectedMethods();
    }

    public function testConstructor()
    {
        $this->assertInstanceOf('Baleen\Migrations\Timeline\TimelineInterface', $this->getInstance());
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
        $instance->upTowards($goal);

        $collection = $this->getTimelineCollection($instance);
        $goalVersion = $collection->get($goal);

        foreach ($collection as $version) {
            /** @var V $version */
            $this->assertTrue($version->isMigrated(), sprintf('Expected version %s to be migrated', $version->getId()));
            if ($version === $goalVersion) {
                break;
            }
        }
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
        $instance->downTowards($goal);

        $collection = $this->getTimelineCollection($instance)->getReverse();
        $goal = $collection->get($goal);

        foreach ($collection as $version) {
            /** @var V $version */
            $this->assertFalse($version->isMigrated(), sprintf('Expected version %s not to be migrated', $version->getId()));
            if ($version === $goal) {
                break;
            }
        }
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
        $instance->goTowards($goal);

        $afterGoal = false;
        /** @var LinkedVersions $versions */
        $versions = new LinkedVersions($this->getTimelineCollection($instance));
        $goal = $versions->get($goal);
        foreach ($versions as $version) {
            /** @var V $version */
            if (!$afterGoal) {
                $this->assertTrue($version->isMigrated(), sprintf('Expected version %s to be migrated', $version->getId()));
            } else {
                $this->assertFalse($version->isMigrated(), sprintf('Expected version %s not to be migrated', $version->getId()));
            }
            if ($version === $goal) {
                $afterGoal = true;
            }
        }
    }

    public function getAllMigratedVersionsFixture()
    {
        return $this->getFixtureFor([
            ['id' => 'v1', 'migrated' => true],
            ['id' => 'v2', 'migrated' => true],
            ['id' => 'v3', 'migrated' => true],
            ['id' => 'v4', 'migrated' => true],
            ['id' => 'v5', 'migrated' => true],
            ['id' => 'v6', 'migrated' => true],
            ['id' => 'v7', 'migrated' => true],
            ['id' => 'v8', 'migrated' => true],
            ['id' => 'v9', 'migrated' => true],
            ['id' => 'v10', 'migrated' => true],
            ['id' => 'v11', 'migrated' => true],
            ['id' => 'v12', 'migrated' => true],
        ]);
    }

    public function getNoMigratedVersionsFixture()
    {
        return $this->getFixtureFor([
            ['id' => 'v1', 'migrated' => false],
            ['id' => 'v2', 'migrated' => false],
            ['id' => 'v3', 'migrated' => false],
            ['id' => 'v4', 'migrated' => false],
            ['id' => 'v5', 'migrated' => false],
            ['id' => 'v6', 'migrated' => false],
            ['id' => 'v7', 'migrated' => false],
            ['id' => 'v8', 'migrated' => false],
            ['id' => 'v9', 'migrated' => false],
            ['id' => 'v10', 'migrated' => false],
            ['id' => 'v11', 'migrated' => false],
            ['id' => 'v12', 'migrated' => false],
        ]);
    }

    public function getMixedVersionsFixture()
    {
        return $this->getFixtureFor([
            ['id' => 'v1', 'migrated' => true],
            ['id' => 'v2', 'migrated' => false],
            ['id' => 'v3', 'migrated' => true],
            ['id' => 'v4', 'migrated' => true],
            ['id' => 'v5', 'migrated' => false],
            ['id' => 'v6', 'migrated' => false],
            ['id' => 'v7', 'migrated' => false],
            ['id' => 'v8', 'migrated' => true],
            ['id' => 'v9', 'migrated' => false],
            ['id' => 'v10', 'migrated' => true],
            ['id' => 'v11', 'migrated' => false],
            ['id' => 'v12', 'migrated' => false],
        ]);
    }

    /**
     * This fixture is meant to cover all use-cases.
     *
     * @return V[]
     */
    public function getFixtureFor(array $versions)
    {
        $migrationMock = m::mock('Baleen\Migrations\Migration\MigrationInterface');
        $migrationMock->shouldReceive('up')->zeroOrMoreTimes();
        $migrationMock->shouldReceive('down')->zeroOrMoreTimes();
        $migrationMock->shouldReceive('abort')->zeroOrMoreTimes();
        $migrationMock->shouldReceive('setOptions')->zeroOrMoreTimes();
        $this->migrationMock = $migrationMock;
        return array_map(function ($arr) use ($migrationMock) {
            $v = new V($arr['id']);
            $v->setMigrated($arr['migrated']);
            $v->setMigration(clone $migrationMock);
            return $v;
        }, $versions);
    }

    /**
     * versionsAndGoalsProvider
     * @return array
     */
    public function versionsAndGoalsProvider()
    {
        $goals = ['v1', 'v8', 'v12', 'first', 'last'];
        $fixtures = [
            $this->getAllMigratedVersionsFixture(),
            $this->getNoMigratedVersionsFixture(),
            $this->getMixedVersionsFixture(),
        ];
        return $this->combinations([$fixtures, $goals]);
    }

    /**
     * @param $timeline
     * @return LinkedVersions
     */
    protected function getTimelineCollection(Timeline $timeline)
    {
        return $this->getPropVal('versions', $timeline);
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
        $version = new V('1');
        $migration = m::mock(MigrationInterface::class);
        $migration->shouldReceive('up')->once();
        $version->setMigration($migration);
        $options = new Options(Options::DIRECTION_UP);

        $dispatcher = new EventDispatcher();
        $timeline = $this->getInstance([$version]);
        $timeline->setEventDispatcher($dispatcher);

        $this->assertSame($dispatcher, $timeline->getEventDispatcher());

        /** @var LinkedVersions $collection */
        $collection = $this->getPropVal('versions', $timeline);

        $dispatcher->addListener(
            EventInterface::COLLECTION_BEFORE,
            function($event, $name) use ($version, $options, $collection, &$listened, $self) {
                $listened[$name] = true;
                $self->assertInstanceOf(EventInterface::class, $event);
                $self->assertInstanceOf(CollectionEvent::class, $event);
                /** @var CollectionEvent $event */
                $self->assertInstanceOf(Progress::class, $event->getProgress());
                $self->assertEquals($collection->count(), $event->getProgress()->getTotal());
                $self->assertEquals(0, $event->getProgress()->getCurrent());

                $self->assertSame($options, $event->getOptions());
                $self->assertSame($collection, $event->getCollection());
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
                    $collection->getPosition($event->getVersion()),
                    $event->getProgress()->getCurrent()
                );

                $self->assertSame($options, $event->getOptions());
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
                    $collection->getPosition($event->getVersion()),
                    $event->getProgress()->getCurrent()
                );

                $self->assertSame($options, $event->getOptions());
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

                $self->assertSame($options, $event->getOptions());
                $self->assertTrue($event->getTarget()->isMigrated());
            }
        );

        $timeline->upTowards($version, $options);

        foreach ($listened as $name => $result) {
            $this->assertTrue($result, sprintf('Expected Timeline to dispatch %s.', $name));
        }
    }

    /**
     * @param $options
     * @dataProvider runSingleProvider
     */
    public function testRunSingle($id, Options $options, $expectation)
    {
        $versions = new LinkedVersions($this->getMixedVersionsFixture());
        $instance = new Timeline($versions);

        $prop = new \ReflectionProperty($instance, 'versions');
        $prop->setAccessible(true);

        $version = $prop->getValue($instance)->get($id);
        /** @var m\Mock $migration */
        $migration = $version->getMigration();

        if ($expectation == 'exception') {
            $this->setExpectedException(TimelineException::class);
        }
        $instance->runSingle($id, $options);

        if ($expectation !== 'exception') {
            $migration->shouldHaveReceived($expectation)->once();
            $this->assertTrue($version->isMigrated() == $options->isDirectionUp());
        }
    }

    /**
     * runSingleProvider
     * @return array
     */
    public function runSingleProvider()
    {
        return [
            ['v1', new Options(Options::DIRECTION_UP)  , 'exception' ], // its already up
            ['v1', new Options(Options::DIRECTION_DOWN), Options::DIRECTION_DOWN],
            ['v2', new Options(Options::DIRECTION_UP)  , Options::DIRECTION_UP],
            ['v2', new Options(Options::DIRECTION_DOWN), 'exception' ], // its already down
        ];
    }

    public function testDoRunUsesMigrationBusToMigrate()
    {
        $migrationBus = m::mock(MigrationBus::class);
        $migrationBus->shouldReceive('handle')->with(m::type(MigrateCommand::class))->once();

        $collection = new LinkedVersions($this->getNoMigratedVersionsFixture());
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
        $versions = V::fromArray(1, 2, 3);
        /** @var MigrationInterface|m\Mock $migration */
        $migration = m::mock(MigrationInterface::class);
        foreach ($versions as $v) {
            $v->setMigration($migration);
        }

        $instance = $this->getInstance($versions);
        $result = $instance->getVersions();

        $this->assertInstanceOf(LinkedVersions::class, $result);
        $this->assertCount(count($versions), $result);
        foreach ($versions as $v) {
            $this->assertSame($v, $result->get($v->getId()));
        }
    }
}
