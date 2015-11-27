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

namespace BaleenTest\Migrations\Service\DomainBus\Factory;

use Baleen\Migrations\Service\DomainBus\Factory\DomainCommandBusFactory;
use Baleen\Migrations\Service\Runner\MigrationRunnerInterface;
use Baleen\Migrations\Service\Runner\MigrationRunner;
use Baleen\Migrations\Service\Runner\RunnerInterface;
use Baleen\Migrations\Shared\Event\PublisherInterface;
use BaleenTest\Migrations\BaseTestCase;
use League\Tactician\CommandBus;
use Mockery as m;

/**
 * Class DomainCommandBusFactoryTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class DomainCommandBusFactoryTest extends BaseTestCase
{
    /**
     * createQuickStartBus
     * @return void
     */
    public function testCreateQuickStartBus()
    {
        $factory = $this->createFactory();
        $bus = $factory->createWithInMemoryLocator();
        $this->assertInstanceOf(CommandBus::class, $bus);
    }

    /**
     * testGetCommandHandlerMapping
     * @return void
     */
    public function testGetCommandHandlerMapping()
    {
        $factory = $this->createFactory();
        $mapping = $factory->getCommandHandlerMapping();

        $canHandle = m::ducktype('handle');
        foreach ($mapping as $key => $object) {
            $this->assertTrue(class_exists($key));
            $this->assertTrue($canHandle->match($object), 'object should have a "handle" function');
        }
    }

    /**
     * createFactory
     * @param PublisherInterface|null $publisher
     * @param RunnerInterface|null $runner
     * @return DomainCommandBusFactory
     */
    private function createFactory(PublisherInterface $publisher = null, RunnerInterface $runner = null) {
        if (null === $publisher) {
            /** @var PublisherInterface|m\Mock $publisher */
            $publisher = m::mock(PublisherInterface::class);
        }
        if (null === $runner) {
            $runner = new MigrationRunner();
        }
        return new DomainCommandBusFactory($publisher, $runner);
    }
}
