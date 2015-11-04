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
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Version as V;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version\Collection\Migrated;
use Baleen\Migrations\Version\Collection\Sortable;
use BaleenTest\Migrations\Version\Collection\SortableTest;
use Mockery as m;
use Zend\Stdlib\ArrayUtils;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class MigratedTest extends SortableTest
{
    /**
     * testAddException
     */
    public function testAddException()
    {
        $version = new Version('1');
        /** @var MigrationInterface|m\Mock $migration */
        $migration = m::mock(MigrationInterface::class);
        $version->setMigration($migration);
        $version->setMigrated(false); // this is what we're testing
        $instance = new Version\Collection\Migrated();

        $this->setExpectedException(CollectionException::class, 'must be migrated');
        $instance->add($version);
    }

    /**
     * testIsUpgradable
     */
    public function testIsUpgradable()
    {
        $versions = Version::fromArray('1', '2', '3', '4', '5');
        foreach ($versions as $version) {
            $version->setMigrated(true);
        }
        $count = count($versions);
        $indexed = new Sortable($versions);
        $upgraded = new Version\Collection\Migrated($indexed);
        $this->assertCount($count, $upgraded);
    }

}
