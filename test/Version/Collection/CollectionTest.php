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

use Baleen\Migrations\Exception\InvalidArgumentException;
use Baleen\Migrations\Exception\Version\Collection\CollectionException;
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Migration\Options\Direction;
use Baleen\Migrations\Shared\Collection\CollectionInterface;
use Baleen\Migrations\Version\Collection\Collection;
use Baleen\Migrations\Version\Collection\Resolver\ResolverInterface;
use Baleen\Migrations\Version\Comparator\ComparatorInterface;
use Baleen\Migrations\Version\Comparator\IdComparator;
use Baleen\Migrations\Version\VersionInterface;
use BaleenTest\Migrations\Shared\Collection\CollectionTestCase;
use Mockery as m;
use Zend\Stdlib\ArrayUtils;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class CollectionTest extends CollectionTestCase
{
    /**
     * testConstructorInvalidArgument
     */
    public function testConstructorInvalidArgument()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        new Collection('test');
    }

    /**
     * testConstructorInvalidItems
     * @return void
     */
    public function testConstructorInvalidItems()
    {
        $this->setExpectedException(CollectionException::class);
        new Collection(['test']);
    }

    /**
     * testConstructorIterator
     */
    public function testConstructorIterator()
    {
        $versions = $this->buildVersions(['1', '2', '3']);
        $iterator = new \ArrayIterator($versions);
        $instance = new Collection($iterator);
        $this->assertCount(3, $instance);
    }

    /**
     * testConstructor
     * @return Collection
     */
    public function testConstructor()
    {
        $collection = new Collection();
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertCount(0, $collection);
        $this->assertTrue($collection->isEmpty());

        $version = $this->buildVersion('1');
        $collection = new Collection([$version]);
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertCount(1, $collection);

        return $collection;
    }

    /**
     * testAddDuplicate
     * @throws CollectionException
     * @throws InvalidArgumentException
     */
    public function testAddDuplicate()
    {
        $version = $this->buildVersion(1);
        $instance = new Collection([$version]);
        $this->setExpectedException(CollectionException::class, 'already exists');
        $instance->add($version);
    }

    /**
     * testReplace
     */
    public function testReplace()
    {
        $versions = $this->buildVersions(range(1, 2));
        $collection = new Collection($versions);
        $this->assertTrue($collection->has('v1'));

        // build a different version 1 object
        $newV1 = $this->buildVersion(1);

        $result1 = $collection->replace($newV1);
        // should have replaced the first version
        $this->assertSame($newV1, $collection->get('v1'));
        $this->assertSame($newV1, $collection->first());
        $this->assertSame($versions[0], $result1);

        // collection should not have a version 3 at the moment
        $this->assertFalse($collection->has('v3'));

        // replace should add version 3 without replacing any versions
        $v3 = $this->buildVersion(3);
        $result2 = $collection->replace($v3);
        $this->assertNull($result2);
        $this->assertSame($v3, $collection->get('v3'));
        $this->assertSame($v3, $collection->last());
    }

    /**
     * testMerge
     */
    public function testMerge()
    {
        $instance1 = new Collection($this->buildVersions(range(1,5)));
        $migrated = $this->buildVersions(['v2', 'v5', 'v6', 'v7']);
        foreach ($migrated as $v) {
            $v->setMigrated(true);
        }
        $instance2 = new Collection($migrated);

        $instance1->merge($instance2);

        foreach ($migrated as $v) {
            $this->assertTrue($instance1->contains($v));
        }
    }

    /**
     * testGetReturnsNullIfNotFound
     */
    public function testGetReturnsNullIfNotFound()
    {
        $versions = $this->buildVersions(['v1', 'v2']);
        $instance = new Collection($versions);
        $this->assertNull($instance->get('v3'));
    }

    /**
     * testGetWithVersion
     */
    public function testGetWithVersion()
    {
        $v = $this->buildVersion(1);
        /** @var Collection|m\Mock $collection */
        $collection = new Collection([$v]);
        $result = $collection->get($this->buildVersion($v));
        $this->assertSame($v, $result);
    }

    /**
     * testArrayAccess
     */
    public function testArrayAccess()
    {
        $instance = new Collection($this->buildVersions(range(100, 102)));
        $this->assertSame('v100', $instance->current()->__toString());
        $this->assertSame('v100', $instance->key());
        $instance->next();
        $this->assertSame('v101', $instance->current()->__toString());
        $this->assertSame('v101', $instance->key());
    }

    /**
     * testInvalidateCache
     */
    public function testInvalidateCache()
    {
        /** @var ResolverInterface|m\Mock $resolver */
        $resolver = m::mock(ResolverInterface::class);
        $instance = new Collection([], $resolver);
        $resolver->shouldReceive('clearCache')->once()->with($instance);
        $this->invokeMethod('invalidateResolverCache', $instance);
    }

    /**
     * testAddInvalidatesCache
     */
    public function testAddInvalidatesCache()
    {
        /** @var ResolverInterface|m\Mock $resolver */
        $resolver = m::mock(ResolverInterface::class);
        $collection = new Collection([], $resolver);
        $resolver->shouldReceive('clearCache')->once()->with($collection);
        $this->invokeMethod('add', $collection, [$this->buildVersion(1)]);
    }

    /**
     * testRemoveInvalidatesCache
     */
    public function testRemoveInvalidatesCache()
    {
        /** @var ResolverInterface|m\Mock $resolver */
        $resolver = m::mock(ResolverInterface::class);
        $resolver->shouldReceive('clearCache');
        $collection = new Collection([$this->buildVersion(1)], $resolver);
        $collection->remove('v1');
        $resolver->shouldHaveReceived('clearCache')->atLeast(1)->with($collection);
    }

    /**
     * testLast
     */
    public function testLast()
    {
        $versions = $this->buildVersions(range(1, 3));
        $instance = new Collection($versions);
        $last = $instance->last();
        $this->assertEquals('v3', $last);
    }

    /**
     * testLast
     */
    public function testFirstLastSorted()
    {
        $versions = $this->buildVersions(range(1, 3));
        $reversedIdComparator = (new IdComparator())->getReverse();
        // will sort versions by id, in reverse order (highest first)
        $collection = new Collection($versions, null, $reversedIdComparator);
        $sorted = $collection->sort();
        $last = $sorted->last();
        $first = $sorted->first();

        $this->assertEquals('v3', $first->getId());
        $this->assertEquals('v1', $last->getId());
    }

    /**
     * testGetSupportsAlias
     */
    public function testFindSupportsAliases()
    {
        $instance = new Collection($this->buildVersions(range(1, 3)));
        $this->assertEquals('v3', $instance->find('last')->getId());
        // also make sure it supports the standard get functionality if no alias is found
        $this->assertEquals('v1', $instance->find('v1')->getId());
    }

    /**
     * IMPROVE: this was refactored to the "resolvers" functionality. Move tests there.
     * @param array $versions
     * @param $alias
     * @param $expectedId
     * @dataProvider getByAliasProvider
     */
    public function testGetByAlias(array $versions, $alias, $expectedId)
    {
        $instance = new Collection($versions);
        $result = $instance->find($alias);
        $this->assertEquals($expectedId, $result->getId());
    }

    /**
     * getByAliasProvider
     * @return array
     */
    public function getByAliasProvider()
    {
        $sample1 = $this->buildVersions(range(1, 5));
        $sample2 = $this->buildVersions(['v097', 'v098', 'v099', 'v100']);
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
        $instance = new Collection([]);
        $result = $instance->getByPosition(1);
        $this->assertNull($result);
    }

    /**
     * testRemoveWhenNoKey
     * @return void
     */
    public function testRemoveWhenNoKeyReturnsNull()
    {
        $collection = new Collection($this->buildVersions(['v1']));
        $result = $collection->remove('v2');
        $this->assertNull($result);
    }

    /**
     * testContainsVersion
     * @return void
     */
    public function testContainsVersion()
    {
        $versions = $this->buildVersions(['v1']);
        $version = $versions[0];
        $collection = new Collection($versions);
        $this->assertTrue($collection->containsVersion($version));
    }

    /**
     * testExists
     * @return void
     */
    public function testExistsSatisfied()
    {
        $versions = $this->buildVersions(range(1,10));
        $versions[8]->setMigrated(true);

        foreach ($versions as $index => $v) {
            /** @var MigrationInterface|m\Mock $migration */
            $migration = $v->getMigration();
            if ($index <= 8) {
                $migration->shouldReceive('up')->once();
            } else {
                $migration->shouldNotReceive('up');
            }
        }

        $collection = new Collection($versions);
        $result = $collection->exists(function ($index, VersionInterface $v) {
            /** @var MigrationInterface|m\Mock $migration */
            $migration = $v->getMigration();
            $migration->up(); // this won't affect the next statement
            return $v->isMigrated();
        });
        $this->assertTrue($result);
    }

    /**
     * testExists
     * @return void
     */
    public function testExistsNotSatisfied()
    {
        $versions = $this->buildVersions(range(1,10));

        foreach ($versions as $v) {
            /** @var MigrationInterface|m\Mock $migration */
            $migration = $v->getMigration();
            $migration->shouldReceive('up')->once();
        }

        $collection = new Collection($versions);
        $result = $collection->exists(function ($index, VersionInterface $v) {
            $v->getMigration()->up();
            return $v->isMigrated();
        });
        $this->assertFalse($result);
    }

    /**
     * testForAllSatisfied
     * @return void
     */
    public function testForAllSatisfied()
    {
        $versions = $this->buildVersions(range(1,5));

        foreach ($versions as $v) {
            /** @var MigrationInterface|m\Mock $migration */
            $migration = $v->getMigration();
            $migration->shouldReceive('up')->once();
        }

        $collection = new Collection($versions);
        $result = $collection->forAll(function ($index, VersionInterface $v) {
            $v->getMigration()->up();
            return !$v->isMigrated();
        });
        $this->assertTrue($result);
    }

    /**
     * testForAllNotSatisfied
     * @return void
     */
    public function testForAllNotSatisfied()
    {
        $versions = $this->buildVersions(range(1,5));

        $versions[3]->setMigrated(true);

        foreach ($versions as $index => $version) {
            /** @var MigrationInterface|m\Mock $migration */
            $migration = $version->getMigration();
            if ($index > 3) {
                $migration->shouldNotReceive('up');
            } else {
                $migration->shouldReceive('up')->once();
            }
        }

        $collection = new Collection($versions);
        $result = $collection->forAll(function ($index, VersionInterface $v) {
            $v->getMigration()->up();
            return !$v->isMigrated();
        });
        $this->assertFalse($result);
    }

    /**
     * testToString
     * @return void
     */
    public function testToString()
    {
        $this->assertNotEmpty((string) new Collection());
    }

    /**
     * testGetValues
     * @return void
     */
    public function testGetValues()
    {
        $versions = $this->buildVersions(range(1,3));
        $collection = new Collection($versions);
        $this->assertEquals($versions, $collection->getValues());
    }

    /**
     * testMap
     * @return void
     */
    public function testMap()
    {
        $expectedIds = array_map(function ($id){ return 'v' . $id;}, range(1,10));
        $collection = new Collection($this->buildVersions(range(1,10)));
        $results = $collection->map(function (VersionInterface $version) {
            return $version->getId()->toString();
        });
        $this->assertSame($expectedIds, array_values($results));
        // since the map essentially creates a key => key array, we can also tests that
        // original keys are preserved when mapping:
        $this->assertSame($expectedIds, array_keys($results));
    }

    /**
     * testFindCastsObjectsToString
     * @return void
     */
    public function testFindCastsObjectsToString()
    {
        $collection = new Collection($this->buildVersions(range(1,3)));
        /** @var VersionInterface|m\Mock $search */
        $search = m::mock($this->buildVersion(1));
        $search->shouldReceive('__toString')->once()->withNoArgs()->andReturn($search->getId()->toString());
        $result = $collection->find($search);
        $this->assertSame($collection->first(), $result);
    }

    /**
     * testGetComparator
     * @return void
     */
    public function testGetComparator()
    {
        /** @var ComparatorInterface|m\Mock $comparator */
        $comparator = m::mock(ComparatorInterface::class);
        $collection = new Collection([], null, $comparator);
        $actual = $collection->getComparator();
        $this->assertSame($comparator, $actual);
    }

    /**
     * testFilter
     * @return void
     */
    public function testFilter()
    {
        $mapToStrings = $this->getMapToStringsCallback();

        $collection = new Collection($this->buildVersions(range(1,4)));
        $this->assertContains('v3', $collection->map($mapToStrings));
        $result = $collection->filter(function(VersionInterface $v) {
            return $v->getId()->toString() != 'v3';
        });
        $this->assertInstanceOf(CollectionInterface::class, $result);
        $this->assertCount(3, $result);
        $this->assertNotContains('v3', $result->map($mapToStrings));
    }

    /**
     * testPartition
     * @param $ids
     * @param $matchesCount
     * @param $noMatchesCount
     * @dataProvider partitionProvider
     */
    public function testPartition(array $ids, $matchesCount, $noMatchesCount)
    {
        $mapToStrings = $this->getMapToStringsCallback();
        $collection = new Collection($this->buildVersions(range(1,4)));
        /** @var CollectionInterface $matches */
        list($matches, $noMatches) = $collection->partition(function($i, VersionInterface $v) use ($ids) {
            return in_array($v->getId()->toString(), $ids);
        });
        $this->assertInstanceOf(CollectionInterface::class, $matches);
        $this->assertInstanceOf(CollectionInterface::class, $noMatches);
        $this->assertEquals($matchesCount, count($matches));
        $this->assertEquals($noMatchesCount, count($noMatches));

        // test that the ids that were matched correspond to the ids from the condition
        $matchedIds = array_values($matches->map($mapToStrings));
        // sort in order to be able to compare them
        sort($matchedIds);
        sort($ids);
        $this->assertEquals($ids, $matchedIds);
    }

    /**
     * partitionProvider
     * @return array
     */
    public function partitionProvider()
    {
        return [
            [[], 0, 4],
            [['v1'], 1, 3],
            [['v2'], 1, 3],
            [['v3'], 1, 3],
            [['v4'], 1, 3],
            [['v1', 'v2'], 2, 2],
            [['v2', 'v3'], 2, 2],
            [['v3', 'v4'], 2, 2],
            [['v1', 'v2', 'v3'], 3, 1],
            [['v2', 'v3', 'v4'], 3, 1],
            [['v3', 'v4', 'v1'], 3, 1],
            [['v4', 'v1', 'v2'], 3, 1],
            [['v1', 'v2', 'v3', 'v4'], 4, 0],
            // no need to test more of the 4
        ];
    }

    /**
     * A callback that maps a Collection to an array of ids
     *
     * @return callable
     */
    private function getMapToStringsCallback() {
        return function (VersionInterface $v) {
            return $v->getId()->toString();
        };
    }
}
