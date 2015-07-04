<?php

namespace BaleenTest\Storage;

use Baleen\Storage\FileStorage;
use Baleen\Version;
use Baleen\Version\Collection;
use BaleenTest\BaseTestCase;
use Mockery as m;

class FileStorageTest extends BaseTestCase
{

    /**
     * @var array This must correspond to versions inside __DIR__ . '/../data/storage.txt'
     */
    protected $versionIds = ['201507020508', '201507020509', '1015', '1', '301507020508'];

    public function testInvalidDirectoryInConstructor()
    {
        $this->setExpectedException('Baleen\Exception\InvalidArgumentException');
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
            /** @var \Baleen\Version\VersionInterface $version */
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
        $instance = new FileStorage($file);
        $instance->writeMigratedVersions($versions);
        $this->assertFileExists($file);
        $contents = explode("\n", file_get_contents($file));
        foreach ($contents as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $this->assertTrue(!empty($versions[$line]), sprintf("File had version '%s', which was not registered in the original collection", $line));
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
