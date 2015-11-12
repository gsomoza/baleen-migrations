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

namespace BaleenTest\Migrations\Version;

use Baleen\Migrations\Exception\InvalidArgumentException;
use Baleen\Migrations\Exception\Version\Collection\CollectionException;
use Baleen\Migrations\Exception\Version\ValidationException;
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version as V;
use Baleen\Migrations\Version\Collection;
use Baleen\Migrations\Version\Collection\Migrated;
use Baleen\Migrations\Version\Collection\Resolver\ResolverInterface;
use Baleen\Migrations\Version\LinkedVersion;
use Baleen\Migrations\Version\Validator\ValidatorInterface;
use Baleen\Migrations\Version\VersionInterface;
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
        $instance = new Collection('test');
        $this->assertInstanceOf(Collection::class, $instance);
    }

    /**
     * testConstructorIterator
     */
    public function testConstructorIterator()
    {
        $versions = V::fromArray(['1', '2', '3']);
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
        $instance = new Collection();
        $this->assertInstanceOf(Collection::class, $instance);
        $this->assertCount(0, $instance);

        $version = new V('1');
        $instance = new Collection([$version]);
        $this->assertInstanceOf(Collection::class, $instance);
        $this->assertCount(1, $instance);

        return $instance;
    }

    /**
     * testAddDuplicate
     * @throws CollectionException
     * @throws InvalidArgumentException
     */
    public function testAddDuplicate()
    {
        $version = new V('1');
        $instance = new Collection([$version]);
        $this->setExpectedException(CollectionException::class, 'already exists');
        $instance->add($version);
    }

    /**
     * testAddOrUpdate
     */
    public function testAddOrUpdate()
    {
        $versions = V::fromArray(range(1, 3));
        $instance = new Collection(array_slice($versions, 0, 2));
        $this->assertTrue($instance->has('v1'));

        $migrated = clone $versions[0];
        $migrated->setMigrated(true);

        $instance->addOrReplace($migrated); // should replace the first version
        $this->assertSame(
            $migrated,
            $instance->get('v1')
        );

        $this->assertFalse($instance->has('3'));
        $instance->addOrReplace($versions[2]);
        $this->assertTrue($instance->has('3'));
    }

    /**
     * testMerge
     */
    public function testMerge()
    {
        $instance1 = new Collection(V::fromArray(range(1,5)));
        $migrated = V::fromArray(['v2', 'v5', 'v6', 'v7']);
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
        $versions = V::fromArray(['v1', 'v2']);
        $instance = new Collection($versions);
        $this->assertNull($instance->get('v3'));
    }

    /**
     * testGetWithVersion
     */
    public function testGetWithVersion()
    {
        $v = m::mock(VersionInterface::class);
        $vId = 'v2';
        /** @var Collection|m\Mock $instance */
        $instance = m::mock(Collection::class)->makePartial();
        $instance->shouldReceive('getById')->once()->with($vId)->andReturn($v);
        $result = $instance->get(new V($vId));
        $this->assertSame($v, $result);
    }

    /**
     * testArrayAccess
     */
    public function testArrayAccess()
    {
        $instance = new Collection(V::fromArray(['v100', 'v101', 'v102']));
        $this->assertSame('v100', $instance->current()->getId());
        $this->assertSame(0, $instance->key());
        $instance->next();
        $this->assertSame('v101', $instance->current()->getId());
        $this->assertSame(1, $instance->key());
    }

    /**
     * testAddThrowsExceptionIfValidateFalse
     * @throws CollectionException
     * @throws InvalidArgumentException
     */
    public function testAddThrowsExceptionIfValidateFalse()
    {
        $version = new V('v1');

        /** @var Collection|m\Mock $instance */
        $instance = m::mock(Collection::class)->makePartial();
        $instance->shouldReceive('validate')->once()->with($version)->andReturn(false);

        $this->setExpectedException(CollectionException::class);

        $instance->add($version);
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
        $instance = new Collection([], $resolver);
        $resolver->shouldReceive('clearCache')->once()->with($instance);
        $this->invokeMethod('add', $instance, [new V('v1')]);
    }

    /**
     * testRemoveInvalidatesCache
     */
    public function testRemoveInvalidatesCache()
    {
        /** @var ResolverInterface|m\Mock $resolver */
        $resolver = m::mock(ResolverInterface::class);
        $instance = new Collection([new V('v1')], $resolver);
        $resolver->shouldReceive('clearCache')->once()->with($instance);
        $this->invokeMethod('remove', $instance, [new V('v1')]);
    }

    /**
     * testAddOnlyAcceptsVersions
     */
    public function testAddOnlyAcceptsVersions()
    {
        $instance = new Collection();
        $version = 'v1';
        $this->setExpectedException(InvalidArgumentException::class);
        $instance->add($version);
    }

    /**
     * testHydrate
     *
     * @param Collection $base
     * @param Collection $update
     * @param array $expectations
     * @param null $exception
     *
     * @dataProvider hydrateProvider
     */
    public function testHydrate(Collection $base, Collection $update, array $expectations, $exception = null)
    {
        if (null !== $exception) {
            $this->setExpectedException($exception);
        }

        $base->hydrate($update);

        foreach ($expectations as $v) {
            /** @var VersionInterface $v */
            $hydrated = $base->getById($v->getId());
            $this->assertSame(get_class($v), get_class($hydrated), 'Error with version ' . $v->getId());
            $this->assertSame($v->isMigrated(), $hydrated->isMigrated(), $v->getId(), 'Error with version ' . $v->getId());
            if ($v instanceof LinkedVersion) {
                /** @var LinkedVersion $v */
                /** @var LinkedVersion $hydrated */
                $this->assertSame($v->getMigration(), $hydrated->getMigration(), 'Error with version ' . $v->getId());
            }
        }
    }

    /**
     * hydrateProvider
     */
    public function hydrateProvider()
    {
        $collection = new Collection([new V('v1', false)]);

        $first = $collection->first();

        /** @var MigrationInterface|m\Mock $migration */
        $migration = m::mock(MigrationInterface::class);
        $second = new LinkedVersion('v2', false, clone $migration);
        $collection->add($second);

        $third = new LinkedVersion('v3', true, clone $migration);
        $collection->add($third);

        $fourth = new V('v4'); // with defaults
        $collection->add($fourth);

        // 1) hydrated with migrated versions
        $migrated = new Collection(V::fromArray(range(1, 3), true));
        $migratedExpectations1 = [
            new V($first->getId(), true), // migrated was set to true
            new LinkedVersion($second->getId(), true, $second->getMigration()), // was set to true, migration didn't change
            new LinkedVersion($third->getId(), true, $third->getMigration()), // was updated, nothing changed
            $fourth, // was not updated
        ];

        // 2) hydrated with linked versions
        $linked = new Collection(LinkedVersion::fromArray(range(1, 3), false, $migration));
        $migrationExpectations2 = [
            new LinkedVersion($first->getId(), false, $migration), // migration was set and type was changed
            new LinkedVersion($second->getId(), false, $migration), // migration was set
            new LinkedVersion($third->getId(), false, $migration), // was set to false, migration was set
            $fourth, // was not updated
        ];

        // 3) Migrated collection hydrated with a non-migrated collection should throw exception
        $migratedVersions = V::fromArray(range(1, 4), true);
        $migrated2 = new Migrated($migratedVersions);
        $notMigrated = new Collection(V::fromArray(range(1, 4)));

        return [
            [$collection, $migrated, $migratedExpectations1],
            [$collection, $linked, $migrationExpectations2],
            [$migrated2, $notMigrated, [], CollectionException::class]
        ];
    }

    /**
     * testIndexOfId
     * @param Collection $collection
     * @param $search
     * @param $expected
     * @dataProvider indexOfIdProvider
     */
    public function testIndexOfId(Collection $collection, $search, $expected)
    {
        $result = $collection->indexOfId($search);
        $this->assertEquals($expected, $result);
    }

    /**
     * indexOfIdProvider
     */
    public function indexOfIdProvider()
    {
        $col1 = new Collection(V::fromArray(range(1,10)));
        $col2 = new Collection([new V('abcd1234'), new V('abcd9876'), new V('1234abcd'), new V('9876xyz')]);
        $col3 = new Collection([new V('abcd1234'), new V('abcd1876'), new V('abcd')]);
        return [
            // normal matches
            [$col1, 'v1', 0],
            [$col1, new V('v2'), 1], // converts parameter to string
            [$col1, 'v10', 9],
            [$col1, 'v11', null],
            // lazy matches
            [$col1, 'v', null],
            [$col2, 'v', null],
            [$col2, 'abcd', null],
            [$col2, 'abcd1', 0],
            [$col2, 'abcd9', 1],
            [$col2, '1', 2],
            [$col2, '9', 3],
            [$col2, 'cd12', null], // middle of the string
            [$col2, 'xyz', null], // should not match end of the string
            // lazy aborts
            [$col3, 'abcd', 2], // lazy aborted, but found exact match afterwards
            [$col3, 'abcd1', null], // lazy aborted and nothing found (test that candidate is reset to null)
        ];
    }

    /**
     * createValidVerion
     * @param string $id
     * @return VersionInterface
     */
    function createValidVersion($id)
    {
        return new Version($id);
    }
}
