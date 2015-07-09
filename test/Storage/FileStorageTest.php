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

use Baleen\Migrations\Storage\FileStorage;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version\Collection;
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

    public function testInvalidDirectoryInConstructor()
    {
        $this->setExpectedException('Baleen\Migrations\Exception\InvalidArgumentException');
        new FileStorage('/non/existent/file');
    }

    /**
     * @param $file
     * @param array $versionIds
     *
     * @dataProvider readMigratedVersionsProvider
     */
    public function testReadMigratedVersions($file, array $versionIds)
    {
        $instance = new FileStorage($file);
        $versions = $instance->readMigratedVersions();
        $this->assertCount(count($versionIds), $versions);
        foreach ($versions as $version) {
            /** @var \Baleen\Migrations\Version\VersionInterface $version */
            $this->assertContains($version->getId(), $versionIds);
        }
    }

    public function readMigratedVersionsProvider()
    {
        return [
            [__DIR__ . '/../data/storage.txt', $this->versionIds]
        ];
    }

    /**
     * @param $file
     * @param Collection $versions
     *
     * @dataProvider writeMigratedVersionsProvider
     */
    public function testWriteMigratedVersions($file, $versions)
    {
        $versions = new Collection($versions);
        $instance = new FileStorage($file);
        $instance->writeMigratedVersions($versions);
        $this->assertFileExists($file);
        $contents = explode("\n", file_get_contents($file));
        foreach ($contents as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $this->assertTrue($versions->has($line), sprintf("File had version '%s', which was not registered in the original collection", $line));
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

}
