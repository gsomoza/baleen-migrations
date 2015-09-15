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

use Baleen\Migrations\Exception\InvalidArgumentException;
use Baleen\Migrations\Exception\StorageException;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version\Collection\MigratedVersions;

/**
 * {@inheritDoc}
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class FileStorage extends AbstractStorage
{
    protected $path;

    /**
     * @param $path
     *
     * @throws InvalidArgumentException
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Reads versions from the storage file.
     *
     * @return Version[]
     *
     * @throws StorageException
     */
    protected function doFetchAll()
    {
        $result = $this->readFile();
        if ($result === false) {
            throw new StorageException(sprintf(
                'Error reading from path "%s"',
                $this->path
            ));
        }
        $contents = explode("\n", $result);
        $versions = [];
        foreach ($contents as $versionId) {
            $versionId = trim($versionId);
            if (!empty($versionId)) { // skip empty lines
                $version = new Version($versionId);
                $versions[] = $version;
            }
        }

        return $versions;
    }

    /**
     * Write a collection of versions to the storage file.
     *
     * @param MigratedVersions $versions
     *
     * @return int
     *
     * @throws StorageException
     */
    public function saveCollection(MigratedVersions $versions)
    {
        $ids = array_map(function (Version $v) {
            return $v->getId();
        }, $versions->toArray());
        $contents = implode("\n", $ids);

        $result = $this->writeFile($contents);
        if ($result === false) {
            throw new StorageException(sprintf(
                'Could not write to file "%s".',
                $this->path
            ));
        }

        return (bool)$result;
    }

    /**
     * @param $contents
     *
     * @return int
     */
    protected function writeFile($contents)
    {
        return file_put_contents($this->path, $contents);
    }

    /**
     * @return string
     */
    protected function readFile()
    {
        return file_get_contents($this->path);
    }

    /**
     * @inheritdoc
     */
    public function save(Version $version)
    {
        $result = false;
        $stored = $this->fetchAll();
        if (!$stored->has($version)) {
            $stored->add($version);
            $result = $this->saveCollection($stored);
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function delete(Version $version)
    {
        $result = false;
        $stored = $this->fetchAll();
        if ($stored->has($version)) {
            $stored->remove($version);
            $result = $this->saveCollection($stored);
        }
        return $result;
    }
}
