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

use Baleen\Migrations\Migration\OptionsInterface;
use Baleen\Migrations\Service\Runner\Event\Collection\CollectionEvent;
use Baleen\Migrations\Common\Collection\CollectionInterface;
use Baleen\Migrations\Version\VersionInterface;
use BaleenTest\Migrations\BaseTestCase;
use Mockery as m;

/**
 * Class CollectionEventTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class CollectionEventTest extends BaseTestCase
{
    /**
     * testGetTarget
     * @return void
     */
    public function testGetTarget()
    {
        /** @var VersionInterface|m\Mock $target */
        $target = m::mock(VersionInterface::class);
        $event = $this->createEvent($target);
        $actual = $event->getTarget();
        $this->assertSame($target, $actual);
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
     * testGetCollection
     * @return void
     */
    public function testGetCollection()
    {
        /** @var CollectionInterface|m\Mock $expected */
        $expected = m::mock(CollectionInterface::class);
        $event = $this->createEvent(null, null, $expected);
        $actual = $event->getCollection();
        $this->assertSame($expected, $actual);
    }

    /**
     * testGetCustomPayload
     * @return void
     */
    public function testGetCustomPayload()
    {
        $event = $this->createEvent();
        $payload = $event->getPayload();
        $this->assertTrue(is_array($payload));
        $this->assertArrayHasKey('occurred_on', $payload);
        $this->assertArrayHasKey('target', $payload);
        $this->assertArrayHasKey('options', $payload);
        $this->assertArrayHasKey('collection', $payload);
        $this->assertSame($event->getOccurredOn(), $payload['occurred_on']);
        $this->assertSame($event->getTarget(), $payload['target']);
        $this->assertSame($event->getOptions(), $payload['options']);
        $this->assertSame($event->getCollection(), $payload['collection']);
    }

    /**
     * createEvent
     * @param VersionInterface|null $target
     * @param OptionsInterface|null $options
     * @param CollectionInterface|null $collection
     * @return CollectionEvent
     */
    private function createEvent(
        VersionInterface $target = null,
        OptionsInterface $options = null,
        CollectionInterface $collection = null)
    {
        if (null === $target) {
            /** @var VersionInterface|m\Mock $target */
            $target = m::mock(VersionInterface::class);
        }
        if (null === $options) {
            /** @var OptionsInterface|m\Mock $collection */
            $options = m::mock(OptionsInterface::class);
        }
        if (null === $collection) {
            /** @var CollectionInterface|m\Mock $collection */
            $collection = m::mock(CollectionInterface::class);
        }
        return new CollectionEvent($target, $options, $collection);
    }
}
