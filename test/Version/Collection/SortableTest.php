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

use Baleen\Migrations\Exception\Version\Collection\CollectionException;
use Baleen\Migrations\Version as V;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version\Collection;
use Baleen\Migrations\Version\Collection\Sortable;
use Baleen\Migrations\Version\VersionInterface;
use BaleenTest\Migrations\Version\CollectionTestCase;
use Mockery as m;
use Zend\Stdlib\ArrayUtils;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class SortableTest extends CollectionTestCase
{
    /**
     * testMerge
     */
    public function testMerge()
    {
        $collection1 = new Sortable(Version::fromArray(range(1, 5)));

        $migrated = Version::fromArray([2, 5, 6, 7]);
        foreach ($migrated as $v) {
            $v->setMigrated(true);
        }
        $collection2 = new Sortable($migrated);

        $collection1->merge($collection2);

        foreach ($migrated as $v) {
            $this->assertTrue($collection1->contains($v));
        }
    }

    /**
     * testAddException
     */
    public function testAddException()
    {
        $version = new Version('1');
        $instance = new Sortable([$version]);

        $this->setExpectedException(CollectionException::class);
        $instance->add($version);
    }

    /**
     * testIsUpgradable
     */
    public function testIsUpgradable()
    {
        $versions = Version::fromArray(range(1, 5));
        $count = count($versions);
        $indexed = new Collection($versions);
        $upgraded = new Sortable($indexed);
        $this->assertCount($count, $upgraded);
    }

    /**
     * testLast
     */
    public function testLast()
    {
        $versions = Version::fromArray(range(1, 3));
        $instance = new Sortable($versions);
        $last = $instance->last();
        $this->assertSame($versions[2], $last);
    }

    /**
     * testGetSupportsAlias
     */
    public function testGetSupportsAlias()
    {
        $instance = new Sortable(Version::fromArray(range(1, 3)));
        $this->assertEquals('v3', $instance->get('last')->getId());
        // also make sure it supports the standard get functionality if no alias is found
        $this->assertEquals('v1', $instance->get('v1')->getId());
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
        $instance = new Sortable($versions);
        $result = $instance->get($alias);
        $this->assertEquals($expectedId, $result->getId());
    }

    /**
     * getByAliasProvider
     * @return array
     */
    public function getByAliasProvider()
    {
        $sample1 = Version::fromArray(range(1, 5));
        $sample2 = Version::fromArray(['v097', 'v098', 'v099', 'v100']);
        return [
            [$sample1, 'last', 'v5'],
            [$sample1, 'first', 'v1'],
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
        $instance = new Sortable([]);
        $result = $instance->getByPosition(1);
        $this->assertNull($result);
    }

    /**
     * testConstructor
     * @return Sortable
     */
    public function testConstructor()
    {
        $instance = new Sortable();
        $this->assertInstanceOf(Collection::class, $instance);
        $this->assertCount(0, $instance);

        $version = new V('1');
        $instance = new Sortable([$version]);
        $this->assertCount(1, $instance);

        return $instance;
    }

    /**
     * createValidVerion
     * @param string $id
     * @return VersionInterface
     */
    public function createValidVersion($id)
    {
        return new V($id);
    }
}
