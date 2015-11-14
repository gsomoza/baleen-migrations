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

namespace BaleenTest\Migrations\Shared\Event;

use Baleen\Migrations\Shared\Event\DomainEventInterface;
use Baleen\Migrations\Shared\Event\MutePublisher;
use Baleen\Migrations\Shared\Event\PublisherInterface;
use BaleenTest\Migrations\BaseTestCase;
use Mockery as m;

/**
 * Class MutePublisherTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class MutePublisherTest extends BaseTestCase
{
    /**
     * testConstructor
     * @return void
     */
    public function testConstructor()
    {
        $publisher = new MutePublisher();
        $this->assertInstanceOf(PublisherInterface::class, $publisher);
    }

    /**
     * testPublish
     * @return void
     */
    public function testPublish()
    {
        /** @var DomainEventInterface|m\Mock $event */
        $event = m::mock(DomainEventInterface::class);
        $publisher = new MutePublisher();
        $publisher->publish($event);
    }
}