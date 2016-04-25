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

namespace BaleenTest\Migrations;

use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Delta\Delta;
use Baleen\Migrations\Delta\DeltaId;
use Baleen\Migrations\Delta\DeltaInterface;
use Mockery as m;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * buildVersionWithMigrations
     * @param $ids
     * @return DeltaInterface[]
     */
    protected function buildVersionWithMigrations($ids)
    {
        if (!is_array($ids)) {
            $ids = func_get_args();
        }
        $versions = $this->buildVersions($ids, false);
        return $versions;
    }

    public function tearDown()
    {
        m::close();
    }

    /**
     * @param $propName
     * @param $instance
     * @return mixed
     */
    public function getPropVal($propName, $instance)
    {
        $prop = new \ReflectionProperty($instance, $propName);
        $prop->setAccessible(true);
        return $prop->getValue($instance);
    }

    /**
     * @param $propName
     * @param $value
     * @param $instance
     */
    public function setPropVal($propName, $value, $instance)
    {
        $prop = new \ReflectionProperty($instance, $propName);
        $prop->setAccessible(true);
        $prop->setValue($instance, $value);
    }

    /**
     * @param $methodName
     * @param $instance
     * @param $args
     * @return mixed
     */
    public function invokeMethod($methodName, $instance, $args = [])
    {
        $method = new \ReflectionMethod($instance, $methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($instance, $args);
    }

    /**
     * @param $arrays
     * @param int $i
     * @return array
     */
    public function combinations($arrays, $i = 0) {
        if (!isset($arrays[$i])) {
            return array();
        }
        if ($i == count($arrays) - 1) {
            return $arrays[$i];
        }

        // get combinations from subsequent arrays
        $tmp = $this->combinations($arrays, $i + 1);

        $result = array();

        // concat each array from tmp with each element from $arrays[$i]
        foreach ($arrays[$i] as $v) {
            foreach ($tmp as $t) {
                $result[] = is_array($t) ?
                    array_merge(array($v), $t) :
                    array($v, $t);
            }
        }

        return $result;
    }

    /**
     * trueFalseProvider
     * @return array
     */
    public function trueFalseProvider()
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * twoTrueFalseProvider
     * @return array
     */
    public function twoTrueFalseProvider()
    {
        return [
            [true, false],
            [false, true],
        ];
    }

    /**
     * buildVersions
     * @param $versions
     * @param bool $migrated
     * @return DeltaInterface[]
     */
    public function buildVersions($versions, $migrated = false) {
        $self = $this;
        return array_map(function($version) use ($migrated, $self) {
            return $self->buildVersion($version, $migrated);
        }, $versions);
    }

    /**
     * buildVersion
     * @param mixed $id
     * @param bool $migrated
     * @param MigrationInterface|null $migration
     * @return Delta
     */
    public function buildVersion($id = null, $migrated = false, MigrationInterface $migration = null)
    {
        if (null === $migration) {
            /** @var MigrationInterface|m\Mock $migration */
            $migration = m::mock(MigrationInterface::class);
        }

        if (null === $id) {
            $id = DeltaId::fromMigration($migration);
        } elseif (is_integer($id)) {
            $id = 'v' . $id;
        } elseif (is_object($id) && $id instanceof DeltaInterface) {
            $id = $id->getId();
        }
        if (!is_object($id)) {
            $id = new DeltaId($id);
        }

        return new Delta($migration, (bool) $migrated, $id);
    }
}
