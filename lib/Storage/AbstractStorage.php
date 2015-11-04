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

namespace Baleen\Migrations\Storage;

use Baleen\Migrations\Exception\StorageException;
use Baleen\Migrations\Version\Collection\Migrated;
use Baleen\Migrations\Version\Comparator\ComparatorAwareInterface;
use Baleen\Migrations\Version\Comparator\ComparatorAwareTrait;
use Baleen\Migrations\Version\VersionInterface;

/**
 * Class AbstractStorage.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
abstract class AbstractStorage implements StorageInterface, ComparatorAwareInterface
{
    use ComparatorAwareTrait;

    /**
     * Reads versions from the storage file.
     *
     * @return Migrated
     *
     * @throws StorageException
     */
    public function fetchAll()
    {
        $versions = $this->doFetchAll();
        if (!is_object($versions) || !$versions instanceof Migrated) {
            foreach ($versions as $version) {
                if (!is_object($version) || !$version instanceof VersionInterface) {
                    throw new StorageException(sprintf(
                        'Expected version to be an instance of %s.',
                        VersionInterface::class
                    ));
                }
                $version->setMigrated(true); // otherwise it wouldn't be stored in the first place
            }
            $collection = new Migrated($versions, null, $this->getComparator());
        } else {
            $collection = $versions;
        }
        return $collection;
    }

    /**
     * @inheritdoc
     */
    public function update(VersionInterface $version)
    {
        $result = null;
        if ($version->isMigrated()) {
            $result = $this->save($version);
        } else {
            $result = $this->delete($version);
        }
        return $result;
    }

    /**
     * @return VersionInterface[]|Migrated
     */
    abstract protected function doFetchAll();
}
