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

namespace BaleenTest\Migrations\Version\Collection;

use Baleen\Migrations\Exception\CollectionException;
use Baleen\Migrations\Version as V;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version\Collection\IndexedVersions;
use Baleen\Migrations\Version\Collection\SortableVersions;
use Mockery as m;
use Zend\Stdlib\ArrayUtils;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class SortableVersionsTest extends IndexedVersionsTest
{


    public function testMerge()
    {
        $instance1 = new SortableVersions(Version::fromArray('1', '2', '3', '4', '5'));
        $migrated = Version::fromArray('2', '5', '6', '7');
        foreach ($migrated as $v) {
            $v->setMigrated(true);
        }
        $instance2 = new SortableVersions($migrated);

        $instance1->merge($instance2);

        foreach ($migrated as $v) {
            $this->assertTrue($instance1->getOrException($v)->isMigrated());
        }
    }

    public function testAddException()
    {
        $version = new V('1');
        $instance = new SortableVersions([$version]);

        $this->setExpectedException(CollectionException::class);
        $instance->add($version);
    }

    public function testIsUpgradable()
    {
        $versions = Version::fromArray('1', '2', '3', '4', '5');
        $count = count($versions);
        $indexed = new IndexedVersions($versions);
        $upgraded = new SortableVersions($indexed);
        $this->assertCount($count, $upgraded);
    }

    public function testLast()
    {
        $versions = Version::fromArray('1', '2', '3');
        $instance = new SortableVersions($versions);
        $last = $instance->last();
        $this->assertSame($versions[2], $last);
    }

    public function testGetSupportsAlias()
    {
        $instance = new SortableVersions(Version::fromArray(1, 2, 3));
        $this->assertEquals(3, $instance->get('latest')->getId());
        // also make sure it supports the standard get functionality if no alias is found
        $this->assertEquals(1, $instance->get(1)->getId());
        $this->assertEquals(99, $instance->get(98, 99));
    }

    /**
     * TODO: this was refactored to the "resolvers" functionality. Move tests there.
     * @param array $versions
     * @param $alias
     * @param $expectedId
     * @dataProvider getByAliasProvider
     */
    public function testGetByAlias(array $versions, $alias, $expectedId)
    {
        $instance = new SortableVersions($versions);
        $result = $instance->get($alias);
        $this->assertEquals($expectedId, $result->getId());
    }

    /**
     * getByAliasProvider
     * @return array
     */
    public function getByAliasProvider()
    {
        $sample1 = Version::fromArray(1, 2, 3, 4, 5);
        $sample2 = Version::fromArray('v097', 'v098', 'v099', 'v100');
        return [
            [$sample1, 'last', 5],
            [$sample1, 'first', 1],
            [$sample2, 'last', 'v100'],
            [$sample2, 'latest', 'v100'],
            [$sample2, 'first', 'v097'],
            [$sample2, 'earliest', 'v097'],
        ];
    }

    /**
     * testGetByPositionEmpty
     */
    public function testGetByPositionReturnsNullWhenNoItems()
    {
        $instance = new SortableVersions([]);
        $result = $instance->getByPosition(1);
        $this->assertNull($result);
    }
}
