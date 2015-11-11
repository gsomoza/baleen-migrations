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

namespace BaleenTest\Migrations\Version\Collection\Resolver;

use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version\Collection;
use Baleen\Migrations\Version\Collection\Resolver\HeadResolver;
use Baleen\Migrations\Version\Collection\Sortable;
use Baleen\Migrations\Version\Comparator\IdComparator;
use Baleen\Migrations\Version\VersionInterface;
use BaleenTest\Migrations\BaseTestCase;
use Mockery as m;

/**
 * Class HeadResolverTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class HeadResolverTest extends BaseTestCase
{
    /**
     * testResolve
     * @param Version[] $versions
     * @param $headId
     *
     * @param string $command
     * @dataProvider resolveProvider
     */
    public function testResolve($versions, $headId, $command = 'HEAD')
    {
        $instance = new HeadResolver();
        $collection = new Sortable($versions, null, new IdComparator());
        $result = $instance->resolve($command, $collection);
        $actual = null !== $result ? $result->getId() : null;
        $this->assertEquals($headId, $actual);
    }

    /**
     * resolveProvider
     * @return array
     */
    public function resolveProvider()
    {
        /** @var MigrationInterface|m\Mock $migration */
        $migration = m::mock(MigrationInterface::class);

        $migratedVersion = new Version('v5', true, clone $migration);

        $oneVersionNoHead = [new Version('v1', false, clone $migration)];
        $oneVersionWithHead = [clone $migratedVersion];

        $tenVersionsNoHead = Version::fromArray(range(1,10));
        $tenVersionsWithHead = Version::fromArray(range(1,10));
        /** @var VersionInterface[] $tenVersionsWithHead */
        $tenVersionsWithHead[8]->setMigrated(true);

        /** @var VersionInterface[] $tenVersionsTwoHeads */
        $tenVersionsTwoHeads = Version::fromArray(range(1,10));
        $tenVersionsTwoHeads[0]->setMigrated(true);
        $tenVersionsTwoHeads[9]->setMigrated(true);

        return [
            [[], null],
            [$oneVersionNoHead, null],
            [$oneVersionWithHead, 'v5'],
            [$oneVersionWithHead, 'v5', 'head'],
            [$tenVersionsNoHead, null],
            [$tenVersionsWithHead, 'v9'],
            [$tenVersionsTwoHeads, 'v10'],
            [$tenVersionsTwoHeads, null, 'notHEAD'],
        ];
    }

    /**
     * testResolveReturnsNull
     */
    public function testResolveReturnsNullNotSortable()
    {
        $instance = new Collection\Resolver\HeadResolver();
        $collection = new Collection(); // not sortable
        $result = $instance->resolve('HEAD', $collection);
        $this->assertNull($result);
    }
}
