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

namespace BaleenTest\Migrations\Delta\Collection;

use Baleen\Migrations\Exception\Version\Collection\CollectionException;
use Baleen\Migrations\Delta\Collection\Collection;
use Baleen\Migrations\Delta\Collection\Migrated;
use BaleenTest\Migrations\Common\Collection\CollectionTestCase;
use Mockery as m;
use Zend\Stdlib\ArrayUtils;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class MigratedTest extends CollectionTestCase
{
    /**
     * testAddException
     */
    public function testAddException()
    {
        $version = $this->buildVersion('v1');
        $instance = new Migrated();

        $this->setExpectedException(CollectionException::class);
        $instance->add($version);
    }

    /**
     * testIsUpgradable
     */
    public function testCanUpgradeFromCollection()
    {
        $versions = $this->buildVersions(range(1, 5), true);
        $count = count($versions);
        $indexed = new Collection($versions);
        $upgraded = new Migrated($indexed);
        $this->assertCount($count, $upgraded);
    }

    /**
     * testConstructor
     * @return Collection
     */
    function testConstructor()
    {
        $instance = new Migrated();
        $this->assertInstanceOf(Collection::class, $instance);
        $this->assertCount(0, $instance);

        $version = $this->buildVersion(1, true);
        $instance = new Migrated([$version]);
        $this->assertCount(1, $instance);

        return $instance;
    }

    /**
     * testIsUpgradable
     */
    public function testIsUpgradable()
    {
        $versions = $this->buildVersions(range(1, 5), true);
        $count = count($versions);
        $sortable = new Collection($versions);
        $upgraded = new Migrated($sortable);
        $this->assertCount($count, $upgraded);
    }
}
