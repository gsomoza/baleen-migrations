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
use Baleen\Migrations\Version\VersionInterface;

/**
 * {@inheritDoc}
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
final class Version implements VersionInterface
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
     * @var MigrationInterface
     */
    private $migration;

    /**
     * @param $id string
     * @param bool $migrated
     * @param MigrationInterface $migration
     *
     * @throws InvalidArgumentException
     */
    public function __construct($id, $migrated = false, MigrationInterface $migration = null)
    {
        $id = trim((string) $id);
        if (empty($id)) {
            throw new InvalidArgumentException('A version\'s id cannot be empty.');
        }
        $this->id = $id;
        $this->migrated = (bool) $migrated;
        $this->migration = $migration;
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function isMigrated()
    {
        return $this->migrated;
    }

    /**
     * {@inheritDoc}
     */
    public function setMigrated($migrated)
    {
        $this->migrated = (bool) $migrated;
    }

    /**
     * {@inheritDoc}
     */
    public function setMigration(MigrationInterface $migration)
    {
        $this->migration = $migration;
    }

    /**
     * Returns the migration associated with this version.
     *
     * @return null|MigrationInterface
     */
    public function getMigration()
    {
        return $this->migration;
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
    public static function fromArray($versionIds, $migrated = false, $migration = null)
    {
        $results = [];
        foreach ($versionIds as $id) {
            if (!is_string($id)) {
                $id = 'v' . (string) $id;
            }
            $results[] = new static($id, $migrated, $migration);
        }

        return $results;
    }

    /**
     * __toString
     * @return string
     */
    public function __toString()
    {
        return $this->getId();
    }

    /**
     * Returns whether the version has a migration class linked to it or not
     *
     * @return bool
     */
    public function hasMigration()
    {
        return $this->getMigration() !== null;
    }
}
