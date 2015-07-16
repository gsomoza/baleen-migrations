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

namespace BaleenTest\Migrations\Storage;

use Baleen\Migrations\Exception\StorageException;
use Baleen\Migrations\Storage\FileStorage;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version\Collection\MigratedVersions;
use BaleenTest\Migrations\BaseTestCase;
use Mockery as m;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class FileStorageTest extends BaseTestCase
{

    /**
     * @var array This must correspond to versions inside __DIR__ . '/../data/storage.txt'
     */
    protected $versionIds = ['201507020508', '201507020509', '1015', '1', '301507020508'];

    /**
     * @param $file
     * @param $versionIdsOrException
     *
     * @dataProvider readMigratedVersionsProvider
     */
    public function testReadMigratedVersions($file, $versionIdsOrException)
    {
        /** @var m::Mock $instance */
        $instance = m::mock(FileStorage::class, [$file])->shouldAllowMockingProtectedMethods()->makePartial();
        if (is_string($versionIdsOrException)) {
            $instance->shouldReceive('readFile')->once()->andReturn(false);
            $this->setExpectedException($versionIdsOrException);
        }
        $versions = $instance->fetchAll();
        $this->assertCount(count($versionIdsOrException), $versions);
        foreach ($versions as $version) {
            /** @var \Baleen\Migrations\Version\VersionInterface $version */
            $this->assertContains($version->getId(), $versionIdsOrException);
        }
    }

    public function readMigratedVersionsProvider()
    {
        return [
            [__DIR__ . '/../data/storage.txt', $this->versionIds],
            ['doesnt matter', StorageException::class],
        ];
    }

    /**
     * @param $file
     * @param MigratedVersions $versions
     *
     * @dataProvider writeMigratedVersionsProvider
     */
    public function testWriteMigratedVersions($file, $versions)
    {
        $versions = new MigratedVersions($versions);
        $instance = new FileStorage($file);
        $instance->saveCollection($versions);
        $this->assertFileExists($file);
        $contents = explode("\n", file_get_contents($file));
        foreach ($contents as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $this->assertTrue(
                    $versions->has($line),
                    sprintf("File had version '%s', which was not registered in the original collection", $line)
                );
            }
        }
        @unlink($file);
    }

    public function writeMigratedVersionsProvider()
    {
        $versions = [];
        foreach ($this->versionIds as $id) {
            $version = new Version($id);
            $version->setMigrated(true);
            $versions[$id] = $version;
        }
        return [
            [__DIR__ . '/../data/output.txt', $versions]
        ];
    }

    public function testFetchAllThrowsExceptionIfNotVersion()
    {
        $instance = m::mock(FileStorage::class)->shouldAllowMockingProtectedMethods()->makePartial();
        $instance->shouldReceive('doFetchAll')->once()->andReturn(['not a version']);
        $this->setExpectedException(StorageException::class, Version::class);
        $instance->fetchAll();
    }

    public function testCantWriteToFileShouldThrowException()
    {
        $versions = new MigratedVersions($this->writeMigratedVersionsProvider()[0][1]);
        $instance = m::mock(FileStorage::class)->shouldAllowMockingProtectedMethods()->makePartial();
        $instance->shouldReceive('writeFile')->once()->andReturn(false);
        $this->setExpectedException(StorageException::class, 'not write');
        $instance->saveCollection($versions);
    }
}
