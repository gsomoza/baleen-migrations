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

namespace BaleenTest\Migrations\Service\Runner\Event;

use Baleen\Migrations\Migration\Options;
use Baleen\Migrations\Migration\OptionsInterface;
use Baleen\Migrations\Service\Runner\Event\Migration\MigrationEvent;
use Baleen\Migrations\Common\Event\Context\CollectionContext;
use Baleen\Migrations\Common\Event\Context\CollectionContextInterface;
use Baleen\Migrations\Common\Event\Progress;
use Baleen\Migrations\Version\VersionInterface;
use BaleenTest\Migrations\BaseTestCase;
use DateTime;
use Mockery as m;

/**
 * Class MigrationEventTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class MigrationEventTest extends BaseTestCase
{
    /**
     * testGetTarget
     * @return void
     */
    public function testGetTarget()
    {
        /** @var VersionInterface|m\Mock $expected */
        $expected = m::mock(VersionInterface::class);
        $event = $this->createEvent($expected);
        $actual = $event->getTarget();
        $this->assertSame($expected, $actual);
    }

    /**
     * testGetOptions
     * @return void
     */
    public function testGetOptions()
    {
        /** @var OptionsInterface|m\Mock $expected */
        $expected = m::mock(OptionsInterface::class);
        $event = $this->createEvent(null, $expected);
        $actual = $event->getOptions();
        $this->assertSame($expected, $actual);
    }

    /**
     * testGetContext
     * @return void
     */
    public function testGetContext()
    {
        /** @var CollectionContextInterface|m\Mock $expected */
        $expected = m::mock(CollectionContextInterface::class);
        $event = $this->createEvent(null, null, $expected);
        $actual = $event->getContext();
        $this->assertSame($expected, $actual);
    }

    /**
     * testGetPayload
     * @return void
     */
    public function testGetPayload()
    {
        $event = $this->createEvent();
        $payload = $event->getPayload();
        $this->assertTrue(is_array($payload));
        $this->assertArrayHasKey('occurred_on', $payload);
        $this->assertArrayHasKey('target', $payload);
        $this->assertArrayHasKey('options', $payload);
        $this->assertArrayHasKey('context', $payload);
        $this->assertSame($event->getOccurredOn(), $payload['occurred_on']);
        $this->assertSame($event->getTarget(), $payload['target']);
        $this->assertSame($event->getOptions(), $payload['options']);
        $this->assertSame($event->getContext(), $payload['context']);
    }

    /**
     * testConstructorCreatesProgressIfNull
     */
    public function testConstructorCreatesProgressIfNull()
    {
        $migrationEvent = new MigrationEvent($this->buildVersion(1), new Options());
        $progress = $migrationEvent->getContext()->getProgress();
        $this->assertInstanceOf(Progress::class, $progress);
        $this->assertEquals(1, $progress->getTotal());
        $this->assertEquals(1, $progress->getCurrent());
    }

    /**
     * testConstructorSetsProgress
     */
    public function testConstructorSetsProgress()
    {
        $context = new CollectionContext(new Progress(10, 5));
        $instance = new MigrationEvent($this->buildVersion(1), new Options(), $context);
        $progress = $instance->getContext()->getProgress();
        $this->assertInstanceOf(Progress::class, $progress);
        $this->assertEquals(10, $progress->getTotal());
        $this->assertEquals(5, $progress->getCurrent());
    }

    /**
     * createEvent
     * @param VersionInterface $target
     * @param OptionsInterface $options
     * @param CollectionContextInterface|null $context
     * @param DateTime|null $occurredOn
     * @return \Baleen\Migrations\Service\Runner\Event\Migration\MigrationEvent
     */
    private function createEvent(
        VersionInterface $target = null,
        OptionsInterface $options = null,
        CollectionContextInterface $context = null,
        DateTime $occurredOn = null
    ) {
        if (null === $target) {
            /** @var VersionInterface|m\Mock $target */
            $target = m::mock(VersionInterface::class);
        }
        if (null === $options) {
            /** @var OptionsInterface|m\Mock $collection */
            $options = m::mock(OptionsInterface::class);
        }
        if (null === $context) {
            /** @var CollectionContextInterface|m\Mock $context */
            $context = m::mock(CollectionContextInterface::class);
        }
        if (null === $occurredOn) {
            /** @var DateTime|m\Mock $occurredOn */
            $occurredOn = m::mock(DateTime::class);
        }
        return new MigrationEvent($target, $options, $context, $occurredOn);
    }
}
