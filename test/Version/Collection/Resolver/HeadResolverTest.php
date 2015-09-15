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

use Baleen\Migrations\Version;
use Baleen\Migrations\Version\Collection\IndexedVersions;
use Baleen\Migrations\Version\Collection\Resolver\HeadResolver;
use Baleen\Migrations\Version\Collection\SortableVersions;
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
        $collection = new SortableVersions($versions);
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
        $migratedVersion = new Version(5);
        $migratedVersion->setMigrated(true);

        $oneVersionNoHead = [new Version(1)];
        $oneVersionWithHead = [clone $migratedVersion];

        $tenVersionsNoHead = Version::fromArray(range(1,10));
        $tenVersionsWithHead = Version::fromArray(range(1,10));
        $tenVersionsWithHead[8]->setMigrated(true);

        $tenVersionsTwoHeads = Version::fromArray(range(1,10));
        reset($tenVersionsTwoHeads)->setMigrated(true);
        end($tenVersionsTwoHeads)->setMigrated(true);

        return [
            [[], null],
            [$oneVersionNoHead, null],
            [$oneVersionWithHead, 5],
            [$oneVersionWithHead, 5, 'head'],
            [$tenVersionsNoHead, null],
            [$tenVersionsWithHead, 9],
            [$tenVersionsTwoHeads, 10],
            [$tenVersionsTwoHeads, null, 'notHEAD'],
        ];
    }

    /**
     * testResolveReturnsNull
     */
    public function testResolveReturnsNullNotSortable()
    {
        $instance = new HeadResolver();
        $collection = new IndexedVersions(); // not sortable
        $result = $instance->resolve('HEAD', $collection);
        $this->assertNull($result);
    }
}
