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

namespace Baleen;

use Baleen\Migration\MigrationInterface;
use Baleen\Version\VersionInterface;

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
    protected $id;

    /**
     * @var bool
     */
    protected $migrated;

    /**
     * @var MigrationInterface
     */
    protected $migration;

    /**
     * Constructor
     *
     * @param $id string
     */
    public function __construct($id)
    {
        $this->id = (string)$id;
    }

    /**
     * @{inheritDoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @{inheritDoc}
     */
    public function isMigrated()
    {
        return $this->migrated;
    }

    /**
     * @{inheritDoc}
     */
    public function setMigrated($migrated)
    {
        $this->migrated = (bool)$migrated;
    }

    /**
     * @{inheritDoc}
     */
    public function setMigration(MigrationInterface $migration)
    {
        $this->migration = $migration;
    }

    /**
     * Returns the migration associated with this version.
     * @return null|MigrationInterface
     */
    public function getMigration()
    {
        return $this->migration;
    }
}
