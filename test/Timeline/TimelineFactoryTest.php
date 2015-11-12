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

namespace BaleenTest\Migrations\Timeline;

use Baleen\Migrations\Exception\InvalidArgumentException;
use Baleen\Migrations\Exception\Version\Collection\CollectionException;
use Baleen\Migrations\Exception\Version\ValidationException;
use Baleen\Migrations\Timeline\TimelineFactory;
use Baleen\Migrations\Timeline\TimelineFactoryInterface;
use Baleen\Migrations\Version as V;
use Baleen\Migrations\Version;
use BaleenTest\Migrations\BaseTestCase;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class TimelineFactoryTest extends BaseTestCase
{
    /**
     * testCreate
     */
    public function testCreate()
    {
        $available = $this->createVersionsWithMigrations(range(1, 5));
        $migrated = Version::fromArray(range(1, 5));
        foreach ($migrated as $v) {
            $v->setMigrated(true);
        }
        $factory = new TimelineFactory();
        $this->assertInstanceOf(TimelineFactoryInterface::class, $factory);
        $timeline = $factory->create($available, $migrated);
        $versions = $timeline->getVersions();
        foreach($versions as $v) {
            $this->assertTrue($v->isMigrated(), sprintf('Expected version %s to be migrated.', $v->getId()));
        }
    }

    /**
     * testCreateThrowsException
     */
    public function testCreateThrowsException()
    {
        $available = $this->createVersionsWithMigrations(range(1, 5));
        // has an additional version that doesn't have a migration:
        $migrated = Version::fromArray(range(1, 6));
        foreach ($migrated as $v) {
            if ($v->getId() !== 'v6') {
                $v->setMigrated(true);
            }
        }

        $factory = new TimelineFactory();

        $this->setExpectedException(CollectionException::class);
        $factory->create($available, $migrated);
    }

    /**
     * testPrepareCollectionInvalidArguments
     * @param $available
     * @param $migrated
     * @dataProvider createInvalidArgumentsProvider
     */
    public function testCreateInvalidArguments($available, $migrated)
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $factory = new TimelineFactory();
        $factory->create($available, $migrated);
    }

    /**
     * testCreateInvalidArgumentsProvider
     * @return array
     */
    public function createInvalidArgumentsProvider()
    {
        return [
            // first invalid
            ['test', []],
            [new \stdClass(), []],
            // second invalid
            [[], 'test'],
            [[], new \stdClass()],
            // both invalid
            ['test', 'test'],
            [new \stdClass(), new \stdClass()],

        ];
    }
}
