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

namespace Baleen\Migrations\Version\Repository;

use Baleen\Migrations\Shared\Collection\CollectionInterface;
use Baleen\Migrations\Version\Repository\Mapper\VersionMapperInterface;
use Baleen\Migrations\Version\VersionId;
use Baleen\Migrations\Version\VersionInterface;

/**
 * Class VersionRepository.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
final class VersionRepository implements VersionRepositoryInterface
{
    /** @var VersionMapperInterface */
    private $mapper;

    /**
     * VersionRepository constructor.
     *
     * @param VersionMapperInterface $mapper
     */
    public function __construct(VersionMapperInterface $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * @inheritdoc
     */
    final public function fetchAll()
    {
        $ids = $this->mapper->fetchAll();

        return array_map(function ($id) {
            if (!is_object($id)) {
                $id = new VersionId($id);
            }
            return $id;
        }, $ids);
    }

    /**
     * @inheritdoc
     */
    final public function update(VersionInterface $version)
    {
        if ($version->isMigrated()) {
            $result = $this->save($version);
        } else {
            $result = $this->delete($version);
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function updateAll(CollectionInterface $versions)
    {
        if ($versions->isEmpty()) {
            return true; // nothing to do - exit early
        }

        $mapToIds = function (VersionInterface $v) {
            return $v->getId();
        };
        /** @var CollectionInterface $migrated */
        /** @var CollectionInterface $notMigrated */
        list($migrated, $notMigrated) = $versions->partition(function ($i, VersionInterface $v) {
            return $v->isMigrated();
        });
        $migratedIds = $migrated->map($mapToIds);
        $notMigratedIds = $notMigrated->map($mapToIds);

        $saveResult = $this->mapper->saveAll($migratedIds);
        $deleteResult = $this->mapper->deleteAll($notMigratedIds);

        return $saveResult && $deleteResult;
    }

    /**
     * @inheritdoc
     */
    public function save(VersionInterface $version)
    {
        return $this->mapper->save($version->getId());
    }

    /**
     * @inheritdoc
     */
    public function delete(VersionInterface $version)
    {
        return $this->mapper->delete($version->getId());
    }

    /**
     * @inheritdoc
     */
    public function fetch($id)
    {
        if (!is_object($id) || !$id instanceof VersionId) {
            $id = new VersionId($id);
        }
        return $this->mapper->fetch($id);
    }
}
