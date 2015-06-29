<?php

namespace BaleenTest\Repository;

use Baleen\Repository\DirectoryRepository;
use BaleenTest\BaseTestCase;
use Mockery as m;

class DirectoryRepositoryTest extends BaseTestCase
{

    /**
     * Test the repository implements RepositoryInterface
     */
    public function testInstanceOfRepositoryInterface()
    {
        $instance = new DirectoryRepository(__DIR__);
        $this->assertInstanceOf('Baleen\Repository\RepositoryInterface', $instance);
    }

    /**
     * @param $directory
     * @param $count
     *
     * @param string $regex
     * @dataProvider fetchAllProvider
     */
    public function testFetchAll($directory, $count, $regex = DirectoryRepository::PATTERN_DEFAULT)
    {
        $instance = new DirectoryRepository($directory, $regex);
        $migrations = $instance->fetchAll();
        $this->assertCount($count, $migrations);
    }

    public function fetchAllProvider()
    {
        $migrationsBase = TEST_BASE_DIR . '/Migrations';
        return [
            [$migrationsBase . '/AllValid', 2],
            [$migrationsBase . '/NoneValid', 0],
            [$migrationsBase . '/CustomRegex', 1, '/Version_([0-9]+).*/'], // custom regex
            // recursive search - should find 4 because there are two migrations in the custom regex directory that
            // conform to the default pattern (to test that they should NOT be loaded with a custom regex)
            [$migrationsBase, 4],
        ];
    }
}
