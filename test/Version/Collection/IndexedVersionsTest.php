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
use Baleen\Migrations\Exception\InvalidArgumentException;
use Baleen\Migrations\Exception\MigrationMissingException;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version as V;
use Baleen\Migrations\Version\Collection\IndexedVersions;
use Baleen\Migrations\Version\Collection\LinkedVersions;
use Baleen\Migrations\Version\Collection\SortableVersions;
use BaleenTest\Migrations\BaseTestCase;
use EBT\Collection\ResourceNotFoundException;
use Mockery as m;
use Zend\Stdlib\ArrayUtils;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class IndexedVersionsTest extends BaseTestCase
{

    public function testConstructorInvalidArgument()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $instance = new IndexedVersions('test');
        $this->assertInstanceOf(IndexedVersions::class, $instance);
    }

    public function testConstructorIterator()
    {
        $versions = Version::fromArray(['1', '2', '3']);
        $iterator = new \ArrayIterator($versions);
        $instance = new IndexedVersions($iterator);
        $this->assertCount(3, $instance);
    }

    public function testConstructor()
    {
        $instance = new IndexedVersions();
        $this->assertInstanceOf(IndexedVersions::class, $instance);
        $this->assertCount(0, $instance);

        $version = new V('1');
        $instance = new IndexedVersions([$version]);
        $this->assertInstanceOf(IndexedVersions::class, $instance);
        $this->assertCount(1, $instance);

        return $instance;
    }

    /**
     * @depends testConstructor
     * @param IndexedVersions $instance
     * @return IndexedVersions
     * @throws CollectionException
     */
    public function testAdd(IndexedVersions $instance)
    {
        $originalCount = count($instance);
        $version2 = new V('2');
        $instance->add($version2);
        $this->assertCount($originalCount + 1, $instance);

        return $instance;
    }

    /**
     * @depends testAdd
     * @param IndexedVersions $instance
     */
    public function testRemove(IndexedVersions $instance)
    {
        $originalCount = count($instance);

        // test remove by version object
        $version = new V('1');
        $instance->remove($version);
        $this->assertCount($originalCount - 1, $instance);

        // test remove by version id
        $instance->remove('2');
        $this->assertCount($originalCount - 2, $instance);
    }

    public function testAddDuplicate()
    {
        $version = new V('1');
        $instance = new IndexedVersions([$version]);
        $this->setExpectedException(CollectionException::class, 'already exists');
        $instance->add($version);
    }

    public function testAddOrUpdate()
    {
        $versions = Version::fromArray('1', '2', '3');
        $instance = new IndexedVersions(array_slice($versions, 0, 2));
        $this->assertTrue($instance->has('1'));

        $migrated = clone $versions[0];
        $migrated->setMigrated(true);

        $instance->addOrReplace($migrated); // should replace the first version
        $this->assertSame(
            $instance->get('1'),
            $migrated
        );

        $this->assertFalse($instance->has('3'));
        $instance->addOrReplace($versions[2]);
        $this->assertTrue($instance->has('3'));
    }

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

    public function testGetOrException()
    {
        $versions = Version::fromArray('1', '2');
        $instance = new SortableVersions($versions);

        $this->setExpectedException(ResourceNotFoundException::class);
        $instance->getOrException('3');
    }

    public function testArrayAccess()
    {
        $instance = new SortableVersions(Version::fromArray('100', '101', '102'));
        $this->assertSame('100', $instance->current()->getId());
        $this->assertSame(100, $instance->key());
        $instance->next();
        $this->assertSame('101', $instance->current()->getId());
        $this->assertSame(101, $instance->key());
        $instance->prev();
        $this->assertSame('100', $instance->current()->getId());
        $this->assertSame(100, $instance->key());
        $instance->end();
        $this->assertSame('102', $instance->current()->getId());
        $this->assertSame(102, $instance->key());
        $instance->rewind();
        $this->assertSame('100', $instance->current()->getId());
        $this->assertSame(100, $instance->key());
    }
}
