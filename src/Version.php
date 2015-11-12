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

namespace Baleen\Migrations;

use Baleen\Migrations\Exception\InvalidArgumentException;
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Version\LinkedVersion;
use Baleen\Migrations\Version\VersionInterface;

/**
 * {@inheritDoc}
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class Version implements VersionInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var bool
     */
    private $migrated;

    /**
     * @param $id string
     * @param bool $migrated
     *
     * @throws InvalidArgumentException
     */
    public function __construct($id, $migrated = false)
    {
        $id = trim((string) $id);
        if (empty($id)) {
            throw new InvalidArgumentException('A version\'s id cannot be empty.');
        }
        $this->id = $id;
        $this->migrated = (bool) $migrated;
    }

    /**
     * {@inheritDoc}
     */
    final public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    final public function isMigrated()
    {
        return $this->migrated;
    }

    /**
     * {@inheritDoc}
     */
    final public function setMigrated($migrated)
    {
        $this->migrated = (bool) $migrated;
    }

    /**
     * {@inheritDoc}
     */
    final public function withMigration(MigrationInterface $migration)
    {
        return new LinkedVersion($this->getId(), $this->isMigrated(), $migration);
    }

    /**
     * Creates a list of versions based on specified IDs.
     *
     * @param mixed $versionIds
     *
     * @param bool $migrated
     * @param null $migration
     * @return Version\VersionInterface[]
     */
    final public static function fromArray($versionIds, $migrated = false, $migration = null)
    {
        $results = [];
        foreach ($versionIds as $id) {
            if (!is_string($id)) {
                $id = 'v' . (string) $id;
            }
            $class = null === $migration ? static::class : LinkedVersion::class;
            $results[] = new $class($id, $migrated, $migration);
        }

        return $results;
    }

    /**
     * __toString
     * @return string
     */
    final public function __toString()
    {
        return $this->getId();
    }
}
