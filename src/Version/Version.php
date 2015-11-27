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
 * <https://github.com/baleen/migrations>.
 */

namespace Baleen\Migrations\Version;

use Baleen\Migrations\Migration\Command\HasMigrationBusTrait;
use Baleen\Migrations\Migration\Command\MigrateCommand;
use Baleen\Migrations\Migration\Command\MigrationBusInterface;
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Migration\OptionsInterface;
use Baleen\Migrations\Shared\EntityInterface;

/**
 * {@inheritDoc}
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
final class Version implements VersionInterface
{
    use HasMigrationBusTrait;

    /** @var VersionId */
    private $id;

    /** @var bool */
    private $migrated;

    /** @var MigrationInterface */
    private $migration;

    /**
     * @param MigrationInterface $migration
     * @param bool $migrated
     * @param null|VersionId $id Optionally force the ID to be something specific.
     * @param null|MigrationBusInterface $bus
     */
    public function __construct(
        MigrationInterface $migration,
        $migrated,
        VersionId $id = null,
        MigrationBusInterface $bus = null
    ) {
        if (null === $id) {
            $id = VersionId::fromMigration($migration);
        }
        $this->id = $id;

        $this->migration = $migration;
        $this->migrated = (bool) $migrated;
        $this->setMigrationBus($bus);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return $this->getId()->toString();
    }

    /**
     * @inheritdoc
     */
    public function isSameIdentityAs(EntityInterface $entity)
    {
        if (!$entity instanceof VersionInterface) {
            return false;
        }

        return $this->getId()->isSameValueAs($entity->getId());
    }

    /**
     * @inheritDoc
     */
    public function isMigrated()
    {
        return $this->migrated;
    }

    /**
     * @inheritdoc
     */
    public function getMigrationClassName()
    {
        return get_class($this->getMigration());
    }

    /**
     * @inheritdoc
     */
    public function getMigrationFileName()
    {
        $class = new \ReflectionClass($this->getMigration());
        $file = $class->getFileName();
        return $file;
    }

    /**
     * @inheritdoc
     */
    public function migrate(OptionsInterface $options)
    {
        $command = new MigrateCommand($this->getMigration(), $options);
        $this->getMigrationBus()->handle($command);
        $this->migrated = $options->getDirection()->isUp();
    }

    /**
     * @return MigrationInterface
     */
    protected function getMigration() {
        return $this->migration;
    }
}
