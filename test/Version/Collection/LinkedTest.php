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
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Version as V;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version\Collection;
use Baleen\Migrations\Version\Collection\Linked;
use Baleen\Migrations\Version\Collection\Sortable;
use Baleen\Migrations\Version\VersionInterface;
use BaleenTest\Migrations\Version\CollectionTestCase;
use Mockery as m;
use Zend\Stdlib\ArrayUtils;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class LinkedTest extends CollectionTestCase
{
    /**
     * testAddException
     */
    public function testAddException()
    {
        $version = new Version('v1');
        $version->setMigrated(true); // but no linked migration
        $instance = new Linked();

        $this->setExpectedException(CollectionException::class, 'must have a Migration');
        $instance->add($version);
    }

    /**
     * testIsUpgradable
     */
    public function testIsUpgradable()
    {
        $versions = $this->createVersionsWithMigrations('1', '2', '3', '4', '5');
        $count = count($versions);
        $sortable = new Sortable($versions);
        $upgraded = new Linked($sortable);
        $this->assertCount($count, $upgraded);
    }

    /**
     * testConstructor
     * @return Collection
     */
    public function testConstructor()
    {
        $instance = new Linked();
        $this->assertInstanceOf(Sortable::class, $instance);
        $this->assertCount(0, $instance);

        $version = $this->createValidVersion('1');
        $instance = new Linked([$version]);
        $this->assertCount(1, $instance);

        return $instance;
    }

    /**
     * createValidVersion
     * @param string $id
     * @return VersionInterface
     */
    public function createValidVersion($id)
    {
        $migration = m::mock(MigrationInterface::class);
        return new V($id, false, $migration);
    }
}
