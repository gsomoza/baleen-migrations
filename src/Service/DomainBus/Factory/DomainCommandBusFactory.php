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

namespace Baleen\Migrations\Service\DomainBus\Factory;

use Baleen\Migrations\Service\DomainBus\DomainBus;
use Baleen\Migrations\Service\DomainBus\DomainBusInterface;
use Baleen\Migrations\Service\DomainBus\Migrate\Collection\CollectionCommand;
use Baleen\Migrations\Service\DomainBus\Migrate\Collection\CollectionHandler;
use Baleen\Migrations\Service\DomainBus\Migrate\Converge\ConvergeCommand;
use Baleen\Migrations\Service\DomainBus\Migrate\Converge\ConvergeHandler;
use Baleen\Migrations\Service\DomainBus\Migrate\Single\SingleCommand;
use Baleen\Migrations\Service\DomainBus\Migrate\Single\SingleHandler;
use Baleen\Migrations\Service\Runner\HasRunnerTrait;
use Baleen\Migrations\Service\Runner\MigrationRunnerInterface;
use Baleen\Migrations\Service\Runner\Factory\CollectionRunnerFactory;
use Baleen\Migrations\Service\Runner\MigrationRunner;
use Baleen\Migrations\Common\Event\PublisherInterface;
use League\Tactician\CommandBus;
use League\Tactician\Handler\Locator\InMemoryLocator;

/**
 * Class DomainCommandBusFactory
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
final class DomainCommandBusFactory
{
    use HasRunnerTrait;

    /** @var PublisherInterface */
    private $publisher;

    /**
     * DomainCommandBusFactory constructor.
     *
     * @param PublisherInterface $publisher
     * @param null|MigrationRunnerInterface $migrationRunner
     */
    public function __construct(PublisherInterface $publisher, MigrationRunnerInterface $migrationRunner = null)
    {
        if (null === $migrationRunner) {
            $migrationRunner = new MigrationRunner($publisher);
        }
        $this->setRunner($migrationRunner);
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
        /** @var MigrationRunnerInterface $migrationRunner */
        $migrationRunner = $this->getRunner();
        $factory = new CollectionRunnerFactory($this->publisher, $migrationRunner);
        return [
            CollectionCommand::class => new CollectionHandler($factory),
            ConvergeCommand::class => new ConvergeHandler(),
            SingleCommand::class => new SingleHandler($migrationRunner),
        ];
    }
}
