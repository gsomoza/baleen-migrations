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
use Baleen\Migrations\Exception\MigrationException;
use Baleen\Migrations\Exception\TimelineException;
use Baleen\Migrations\Migration\Options;
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Timeline;
use Baleen\Migrations\Version as V;
use Baleen\Migrations\Version\Comparator\DefaultComparator;
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
     * @param null $callable
     * @return m\Mock|\Baleen\Migrations\Timeline
     */
    public function getInstance($versions = [], $callable = null)
    {
        if (null === $callable) {
            $callable = new DefaultComparator();
        }
        return m::mock('Baleen\Migrations\Timeline', [$versions, $callable])->makePartial()->shouldAllowMockingProtectedMethods();
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

        $versions = $this->getInstanceVersions($instance);
        foreach ($versions as $version) {
            /** @var V $version */
            $this->assertTrue($version->isMigrated(), sprintf('Expected version %s to be migrated', $version->getId()));
            if ($version->getId() == $goal) {
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
    public function testDownTowards($versions, $goal)
    {
        $instance = $this->getInstance($versions);
        $instance->downTowards($goal);

        $versions = $this->getInstanceVersions($instance);
        $versions = array_reverse($versions);
        foreach ($versions as $version) {
            /** @var V $version */
            $this->assertFalse($version->isMigrated(), sprintf('Expected version %s not to be migrated', $version->getId()));
            if ($version->getId() == $goal) {
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
        $versions = $this->getInstanceVersions($instance);
        foreach ($versions as $version) {
            /** @var V $version */
            if (!$afterGoal) {
                $this->assertTrue($version->isMigrated(), sprintf('Expected version %s to be migrated', $version->getId()));
            } else {
                $this->assertFalse($version->isMigrated(), sprintf('Expected version %s not to be migrated', $version->getId()));
            }
            if ($version->getId() == $goal) {
                $afterGoal = true;
            }
        }
    }

    public function getAllMigratedVersionsFixture()
    {
        return $this->getFixtureFor([
            ['id' => 1, 'migrated' => true],
            ['id' => 2, 'migrated' => true],
            ['id' => 3, 'migrated' => true],
            ['id' => 4, 'migrated' => true],
            ['id' => 5, 'migrated' => true],
            ['id' => 6, 'migrated' => true],
            ['id' => 7, 'migrated' => true],
            ['id' => 8, 'migrated' => true],
            ['id' => 9, 'migrated' => true],
            ['id' => 10, 'migrated' => true],
            ['id' => 11, 'migrated' => true],
            ['id' => 12, 'migrated' => true],
        ]);
    }

    public function getNoMigratedVersionsFixture()
    {
        return $this->getFixtureFor([
            ['id' => 1, 'migrated' => false],
            ['id' => 2, 'migrated' => false],
            ['id' => 3, 'migrated' => false],
            ['id' => 4, 'migrated' => false],
            ['id' => 5, 'migrated' => false],
            ['id' => 6, 'migrated' => false],
            ['id' => 7, 'migrated' => false],
            ['id' => 8, 'migrated' => false],
            ['id' => 9, 'migrated' => false],
            ['id' => 10, 'migrated' => false],
            ['id' => 11, 'migrated' => false],
            ['id' => 12, 'migrated' => false],
        ]);
    }

    public function getMixedVersionsFixture()
    {
        return $this->getFixtureFor([
            ['id' => 1, 'migrated' => true],
            ['id' => 2, 'migrated' => false],
            ['id' => 3, 'migrated' => true],
            ['id' => 4, 'migrated' => true],
            ['id' => 5, 'migrated' => false],
            ['id' => 6, 'migrated' => false],
            ['id' => 7, 'migrated' => false],
            ['id' => 8, 'migrated' => true],
            ['id' => 9, 'migrated' => false],
            ['id' => 10, 'migrated' => true],
            ['id' => 11, 'migrated' => false],
            ['id' => 12, 'migrated' => false],
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

    public function versionsAndGoalsProvider()
    {
        return [
            [$this->getAllMigratedVersionsFixture(), 12],
            [$this->getAllMigratedVersionsFixture(), 1],
            [$this->getAllMigratedVersionsFixture(), 8],
            [$this->getAllMigratedVersionsFixture(), 9],
            [$this->getNoMigratedVersionsFixture(), 12],
            [$this->getNoMigratedVersionsFixture(), 1],
            [$this->getNoMigratedVersionsFixture(), 8],
            [$this->getNoMigratedVersionsFixture(), 9],
            [$this->getMixedVersionsFixture(), 12],
            [$this->getMixedVersionsFixture(), 1],
            [$this->getMixedVersionsFixture(), 8],
            [$this->getMixedVersionsFixture(), 9],
        ];
    }

    /**
     * @param $instance
     * @return mixed
     */
    protected function getInstanceVersions($instance)
    {
        $prop = new \ReflectionProperty($instance, 'versions');
        $prop->setAccessible(true);
        $versions = $prop->getValue($instance);
        return $versions->toArray();
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

        $prop = new \ReflectionProperty($timeline, 'versions');
        $prop->setAccessible(true);
        $collection = $prop->getValue($timeline);

        $dispatcher->addListener(
            EventInterface::COLLECTION_BEFORE,
            function($event, $name) use ($version, $options, $collection, &$listened, $self) {
                $listened[$name] = true;
                $self->assertInstanceOf(EventInterface::class, $event);
                $self->assertInstanceOf(CollectionEvent::class, $event);
                /** @var CollectionEvent $event */
                $self->assertSame($options, $event->getOptions());
                $self->assertSame($collection, $event->getCollection());
                // the following also asserts that the version is NOT migrated
                $self->assertSame($version, $event->getTarget());
            }
        );
        $dispatcher->addListener(
            EventInterface::MIGRATION_BEFORE,
            function($event, $name) use ($version, $options, &$listened, $self) {
                $listened[$name] = true;
                $self->assertInstanceOf(EventInterface::class, $event);
                $self->assertInstanceOf(MigrationEvent::class, $event);
                /** @var MigrationEvent $event */
                $self->assertSame($options, $event->getOptions());
                // the following also asserts that the version is NOT migrated
                $self->assertSame($version, $event->getVersion());
            }
        );
        $dispatcher->addListener(
            EventInterface::MIGRATION_AFTER,
            function($event, $name) use ($version, $options, &$listened, $self) {
                $listened[$name] = true;
                $self->assertInstanceOf(EventInterface::class, $event);
                $self->assertInstanceOf(MigrationEvent::class, $event);
                /** @var MigrationEvent $event */
                $self->assertSame($options, $event->getOptions());
                $self->assertTrue($event->getVersion()->isMigrated());
            }
        );
        $dispatcher->addListener(
            EventInterface::COLLECTION_AFTER,
            function($event, $name) use ($version, $options, &$listened, $self) {
                $listened[$name] = true;
                $self->assertInstanceOf(EventInterface::class, $event);
                $self->assertInstanceOf(CollectionEvent::class, $event);
                /** @var CollectionEvent $event */
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
        $instance = new Timeline($this->getMixedVersionsFixture());

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

    public function testRunSingleExceptionIfNoMigration()
    {
        $versions = [new V('1')];
        $instance = new Timeline($versions);

        $this->setExpectedException(MigrationException::class);
        $instance->runSingle($versions[0], new Options(Options::DIRECTION_UP));
    }

    public function runSingleProvider()
    {
        return [
            ['1', new Options(Options::DIRECTION_UP)  , 'exception' ], // its already up
            ['1', new Options(Options::DIRECTION_DOWN), Options::DIRECTION_DOWN],
            ['2', new Options(Options::DIRECTION_UP)  , Options::DIRECTION_UP],
            ['2', new Options(Options::DIRECTION_DOWN), 'exception' ], // its already down
        ];
    }
}
