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

namespace Baleen\Migrations\Service\Command\Factory;

use Baleen\Migrations\Service\Command\DomainBus;
use Baleen\Migrations\Service\Command\DomainBusInterface;
use Baleen\Migrations\Service\Command\Migrate\Collection\CollectionCommand;
use Baleen\Migrations\Service\Command\Migrate\Collection\CollectionHandler;
use Baleen\Migrations\Service\Command\Migrate\Converge\ConvergeCommand;
use Baleen\Migrations\Service\Command\Migrate\Converge\ConvergeHandler;
use Baleen\Migrations\Service\Command\Migrate\Single\SingleCommand;
use Baleen\Migrations\Service\Command\Migrate\Single\SingleHandler;
use Baleen\Migrations\Service\Runner\Factory\CollectionRunnerFactory;
use Baleen\Migrations\Service\Runner\MigrationRunner;
use Baleen\Migrations\Shared\Event\PublisherInterface;
use League\Tactician\CommandBus;
use League\Tactician\Handler\Locator\InMemoryLocator;

/**
 * Class DomainCommandBusFactory
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
final class DomainCommandBusFactory
{
    /** @var MigrationRunner */
    private $migrationRunner;

    /** @var PublisherInterface */
    private $publisher;

    /**
     * DomainCommandBusFactory constructor.
     *
     * @param PublisherInterface $publisher
     * @param MigrationRunner $migrationRunner
     */
    public function __construct(PublisherInterface $publisher, MigrationRunner $migrationRunner)
    {
        $this->migrationRunner = $migrationRunner;
        $this->publisher = $publisher;
    }

    /**
     * create
     *
     * @return DomainBusInterface
     */
    public function createWithInMemoryLocator()
    {
        $locator = new InMemoryLocator($this->getCommandHandlerMapping());
        return DomainBus::createWithLocator($locator);
    }

    /**
     * getCommandHandlerMapping
     *
     * @return array
     */
    public function getCommandHandlerMapping()
    {
        $factory = new CollectionRunnerFactory($this->publisher, $this->migrationRunner);
        return [
            CollectionCommand::class => new CollectionHandler($factory),
            ConvergeCommand::class => new ConvergeHandler(),
            SingleCommand::class => new SingleHandler($this->migrationRunner),
        ];
    }
}
