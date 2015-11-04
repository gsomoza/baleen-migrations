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

use Baleen\Migrations\Exception\CollectionException;
use Baleen\Migrations\Exception\InvalidArgumentException;
use Baleen\Migrations\Version as V;
use Baleen\Migrations\Version\Collection;
use Baleen\Migrations\Version\Collection\Resolver\ResolverInterface;
use Baleen\Migrations\Version\Collection\Sortable;
use BaleenTest\Migrations\BaseTestCase;
use Mockery as m;
use Zend\Stdlib\ArrayUtils;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class CollectionTest extends BaseTestCase
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
     * @depends testConstructor
     * @param Collection $instance
     * @return Collection
     * @throws CollectionException
     */
    public function testAdd(Collection $instance)
    {
        $originalCount = count($instance);
        $version2 = new V('2');
        $instance->add($version2);
        $this->assertCount($originalCount + 1, $instance);

        return $instance;
    }

    /**
     * @depends testAdd
     * @param Collection $instance
     */
    public function testRemove(Collection $instance)
    {
        $originalCount = $instance->count();

        // test remove by version object
        $version = $instance->getById('1');
        $instance->removeElement($version);
        $this->assertCount($originalCount - 1, $instance);

        // test remove by  index
        $version = $instance->first(); // and only
        $index = $instance->indexOf($version);
        $instance->remove($index);
        $this->assertCount($originalCount - 2, $instance);
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
        $versions = V::fromArray('v1', 'v2', 'v3');
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
        $instance1 = new Sortable(V::fromArray('1', '2', '3', '4', '5'));
        $migrated = V::fromArray('2', '5', '6', '7');
        foreach ($migrated as $v) {
            $v->setMigrated(true);
        }
        $instance2 = new Sortable($migrated);

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
        $versions = V::fromArray('v1', 'v2');
        $instance = new Sortable($versions);
        $this->assertNull($instance->get('v3'));
    }

    /**
     * testArrayAccess
     */
    public function testArrayAccess()
    {
        $instance = new Sortable(V::fromArray('v100', 'v101', 'v102'));
        $this->assertSame('v100', $instance->current()->getId());
        $this->assertSame(0, $instance->key());
        $instance->next();
        $this->assertSame('v101', $instance->current()->getId());
        $this->assertSame(1, $instance->key());
    }

    public function testAddThrowsExceptionIfValidateFalse()
    {
        $version = new V(1);
        $instance = m::mock(Collection\Sortable::class)->makePartial();
        $instance->shouldReceive('validate')->with($version)->once()->andReturn(false);
        $this->setExpectedException(
            CollectionException::class,
            'Validate should either return true or throw an exception'
        );
        $instance->add($version);
    }

    /**
     * testInvalidateCache
     */
    public function testInvalidateCache()
    {
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
        $resolver = m::mock(ResolverInterface::class);
        $instance = new Collection([new V('v1')], $resolver);
        $resolver->shouldReceive('clearCache')->once()->with($instance);
        $this->invokeMethod('remove', $instance, [new V('v1')]);
    }
}
