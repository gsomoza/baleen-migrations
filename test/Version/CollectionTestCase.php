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
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Version as V;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version\Collection;
use Baleen\Migrations\Version\Collection\Linked;
use Baleen\Migrations\Version\Collection\Migrated;
use Baleen\Migrations\Version\Collection\Resolver\ResolverInterface;
use Baleen\Migrations\Version\Collection\Sortable;
use Baleen\Migrations\Version\VersionInterface;
use BaleenTest\Migrations\BaseTestCase;
use Mockery as m;
use Zend\Stdlib\ArrayUtils;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
abstract class CollectionTestCase extends BaseTestCase
{
    /**
     * testConstructor
     * @return Collection
     */
    abstract public function testConstructor();

    /**
     * createValidVersion
     * @param string $id
     * @return VersionInterface
     */
    abstract public function createValidVersion($id);

    /**
     * @depends testConstructor
     * @param Collection $instance
     * @return Collection
     * @throws CollectionException
     */
    public function testAdd(Collection $instance)
    {
        $originalCount = count($instance);
        $version2 = $this->createValidVersion('v2');
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
     * setMigrated
     * @param array|Collection $collection
     * @param bool $value
     * @param int|null $offset
     * @param int|null $length
     */
    protected function setMigrated($collection, $value, $offset = 0, $length = null)
    {
        if (is_array($collection)) {
            $collection = array_slice($collection, $offset, $length);
        } else {
            /** @var Collection $collection */
            $collection = $collection->slice($offset, $length);
        }
        foreach ($collection as $version) {
            /** @var VersionInterface $version */
            $version->setMigrated((bool) $value);
        }
    }

    /**
     * setMigration
     * @param array|Collection $collection
     * @param MigrationInterface $migration
     * @param int|null $offset
     * @param int|null $length
     */
    protected function setMigration($collection, MigrationInterface $migration, $offset = 0, $length = null)
    {
        if (is_array($collection)) {
            $collection = array_slice($collection, $offset, $length);
        } else {
            /** @var Collection $collection */
            $collection = $collection->slice($offset, $length);
        }
        foreach ($collection as $version) {
            /** @var VersionInterface $version */
            $version->setMigration($migration);
        }
    }
}
