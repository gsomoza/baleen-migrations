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

namespace BaleenTest\Migrations\Delta\Collection\Resolver;

use Baleen\Migrations\Delta\Collection\Collection;
use Baleen\Migrations\Delta\Collection\Resolver\HeadResolver;
use Baleen\Migrations\Delta\Comparator\IdComparator;
use Baleen\Migrations\Delta\DeltaInterface;
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
     * @param \Baleen\Migrations\Delta\Delta[] $versions
     * @param $expectedHeadId
     *
     * @param string $command
     * @dataProvider resolveProvider
     */
    public function testResolve($versions, $expectedHeadId, $command = 'HEAD')
    {
        $resolver = new HeadResolver();
        $collection = new Collection($versions, null, new IdComparator());
        $head = $resolver->resolve($command, $collection);
        $actual = null !== $head ? $head->getId() : null;
        $this->assertEquals($expectedHeadId, $actual);
    }

    /**
     * resolveProvider
     * @return array
     */
    public function resolveProvider()
    {
        $migratedVersion = $this->buildVersion('v5', true);

        $oneVersionNoHead = [$this->buildVersion('v1', false)];
        $oneVersionWithHead = [clone $migratedVersion];

        $nineVersionsNoHead = $this->buildVersions(range(1,9));
        $nineVersionsWithHead = $this->buildVersions(range(1,9));
        /** @var DeltaInterface[] $nineVersionsWithHead */
        $nineVersionsWithHead[8] = $this->buildVersion(9, true);

        /** @var DeltaInterface[] $nineVersionsTwoHeads */
        $nineVersionsTwoHeads = $this->buildVersions(range(1,9));
        $nineVersionsTwoHeads[0] = $this->buildVersion(1, true);
        $nineVersionsTwoHeads[8] = $this->buildVersion(9, true);

        return [
            [[], null],
            [$oneVersionNoHead, null],
            [$oneVersionWithHead, 'v5'],
            [$oneVersionWithHead, 'v5', 'head'],
            [$nineVersionsNoHead, null],
            [$nineVersionsWithHead, 'v9'],
            [$nineVersionsTwoHeads, 'v9'],
            [$nineVersionsTwoHeads, null, 'notHEAD'],
        ];
    }

    /**
     * testResolveReturnsNull
     */
    public function testResolveReturnsNullNotSortable()
    {
        $instance = new HeadResolver();
        $collection = new Collection(); // not sortable
        $result = $instance->resolve('HEAD', $collection);
        $this->assertNull($result);
    }
}
