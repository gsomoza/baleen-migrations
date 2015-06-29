<?php

namespace BaleenTest\Version;

use Baleen\Timeline\TimelineFactory;
use Baleen\Version;
use Baleen\Version\Collection;
use BaleenTest\BaseTestCase;
use Mockery as m;

class TimelineFactoryTest extends BaseTestCase
{
    private $availableMock;
    private $migratedMock;

    public function setUp()
    {
        $this->availableMock = [];
        $this->migratedMock = [];
    }

    public function testConstructor()
    {
        $instance = new TimelineFactory($this->availableMock, $this->migratedMock);
        $this->assertInstanceOf('Baleen\Timeline\TimelineFactory', $instance);
    }

    /**
     * @param $migrated
     * @param $available
     *
     * @dataProvider createProvider
     * //TODO: finish data provider and re-enable this test
     */
    public function _testCreate($migrated, $available)
    {
        $instance = new TimelineFactory($migrated, $available);
        $result = $instance->create();
        $this->assertInstanceOf('Baleen\Timeline', $result);
    }

    public function createProvider()
    {

    }
}
