<?php

use BaleenTest\BaseTestCase;

use Baleen\Version as V;

class TimelineFactoryTest extends BaseTestCase
{

    public function testCreate()
    {
        $factory = new \Baleen\Timeline\TimelineFactory(
            [ 1 => new V(1), 2 => new V(2), 3 => new V(3), 4 => new V(4), 5 => new V(5) ],
            [ 1 => new V(1), 3 => new V(3), 4 => new V(4)]
        );
        $timeline = $factory->create();
        $prop = new \ReflectionProperty($timeline, 'versions');
        $prop->setAccessible(true);
        $versions = $prop->getValue($timeline);
        $expectedMigrated = [1 => 1, 2 => 0, 3 => 1, 4 => 1, 0];
        $this->assertEquals($expectedMigrated, array_map(function(V $v) {
            return $v->isMigrated() ? 1 : 0;
        }, $versions));
    }
}
