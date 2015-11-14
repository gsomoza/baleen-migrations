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

namespace Baleen\Migrations\Migration\Repository\Mapper;
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Shared\ValueObjectInterface;
use Baleen\Migrations\Version\VersionId;

/**
 * Class Definition
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
final class Definition implements DefinitionInterface
{
    /** @var VersionId */
    private $id;

    /** @var MigrationInterface */
    private $migration;

    /**
     * Definition constructor.
     * @param MigrationInterface $migration
     * @param null|string|ValueObjectInterface $id Overrides the ID for the migration with the specified ID
     */
    public function __construct(MigrationInterface $migration, $id = null)
    {
        if (null === $id) {
            $id = VersionId::fromMigration($migration);
        } else {
            $id = new VersionId((string) $id);
        }
        $this->id = $id;

        $this->migration = $migration;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return MigrationInterface
     */
    public function getMigration()
    {
        return $this->migration;
    }
}
