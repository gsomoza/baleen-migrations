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

namespace Baleen\Migrations\Service\Runner\Factory;

use Baleen\Migrations\Service\Runner\CollectionRunner;
use Baleen\Migrations\Service\Runner\RunnerInterface;
use Baleen\Migrations\Shared\Collection\CollectionInterface;
use Baleen\Migrations\Shared\Event\PublisherInterface;

/**
 * Class CollectionRunnerFactory
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
final class CollectionRunnerFactory implements CollectionRunnerFactoryInterface
{
    /** @var PublisherInterface */
    private $publisher;

    /** @var RunnerInterface */
    private $migrationRunner;

    /**
     * CollectionRunnerFactory constructor.
     * @param PublisherInterface $publisher
     * @param RunnerInterface $migrationRunner
     */
    public function __construct(PublisherInterface $publisher = null, RunnerInterface $migrationRunner = null)
    {
        $this->publisher = $publisher;
        $this->migrationRunner = $migrationRunner;
    }

    /**
     * Creates a CollectionRunner for the specified collection
     *
     * @param CollectionInterface $collection
     * @return RunnerInterface
     */
    public function create(CollectionInterface $collection)
    {
        return new CollectionRunner($collection, $this->migrationRunner, $this->publisher);
    }
}
