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
use Baleen\Migrations\Version\Collection\Resolver\OffsetResolver;
use Baleen\Migrations\Version\Collection\SortableVersions;
use BaleenTest\Migrations\BaseTestCase;
use Mockery as m;

/**
 * Class OffsetResolverTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class OffsetResolverTest extends BaseTestCase
{
    /**
     * testResolve
     * @param $versions
     * @param $alias
     * @param $expected
     *
     * @dataProvider resolveProvider
     */
    public function testResolve($versions, $alias, $expected)
    {
        $instance = new OffsetResolver();
        $collection = new SortableVersions($versions, $instance);
        $result = $instance->resolve($alias, $collection);
        $actual = $result ? $result->getId() : null;
        $this->assertEquals($expected, $actual);
    }

    /**
     * resolveProvider
     */
    public function resolveProvider()
    {
        $versions = Version::fromArray(1,2,3,4,5,6,7,8,9,10,15);
        return [
            [[], '0', null],
            [[], '1', null],
            [[], '1+', null],
            [$versions, '0-3', null],
            [$versions, '0', null],
            [$versions, '0+2', null],
            [$versions, '1+', 2],
            [$versions, '1++', 3],
            [$versions, '1+2', 3],
            [$versions, '1-', null],
            [$versions, '5-', 4],
            [$versions, '5~', 4],
            [$versions, '5--', 3],
            [$versions, '5~~', 3],
            [$versions, '5-4', 1],
            [$versions, '5-5', null],
            [$versions, '5+++', 8],
            [$versions, '5+4', 9],
            [$versions, '5+9', null],
            [$versions, '10+', 15],
            [$versions, '10++', null],
            [$versions, '10-3', 7],
            // override operator count
            [$versions, '5---2', 3],
            [$versions, '5~~~2', 3],
            [$versions, '5+++4', 9],
            // test if there's a gap (here between 10 and 15)
            [$versions, '5+6', 15],
            [$versions, '10+', 15],
            [$versions, '10+1', 15],
            [$versions, '15+', null],
            [$versions, '15-', 10],
            [$versions, '15-1', 10],
            // unsupported aliases (by this particular resolver)
            [$versions, null, null],
            [$versions, '', null],
            [$versions, '-3', null],
            [$versions, '+3', null],
            [$versions, '1', null],
            [$versions, '5', null],
            [$versions, '15', null],
            [$versions, 'abc', null],
            [$versions, '+++', null],
        ];
    }

    /**
     * testOnlyResolvesIfSortable
     */
    public function testOnlyResolvesIfSortable()
    {
        $instance = new OffsetResolver();
        $collection = new Version\Collection\IndexedVersions(Version::fromArray(1,2,3));
        $result = $instance->resolve('1+', $collection);
        $this->assertNull($result);
    }
}
