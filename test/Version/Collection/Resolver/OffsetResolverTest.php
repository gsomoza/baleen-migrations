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
use Baleen\Migrations\Version\Collection\Sortable;
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
        $collection = new Sortable($versions, $instance);
        $result = $instance->resolve($alias, $collection);
        $actual = $result ? $result->getId() : null;
        $this->assertEquals($expected, $actual);
    }

    /**
     * resolveProvider
     */
    public function resolveProvider()
    {
        $versions = Version::fromArray('v01','v02','v03','v04','v05','v06','v07','v08','v09','v10','v15');
        return [
            [[], '0', null], // 0 will get the first element (internally the collection is a 0-indexed array)
            [$versions, '0', null], // no offset pattern
            [$versions, '0+', 'v02'],
            [$versions, '0+++', 'v04'],
            [$versions, '0+2', 'v03'],
            [$versions, '0-', null],
            [$versions, '4-', 'v04'],
            [$versions, '4~', 'v04'],
            [$versions, '4--', 'v03'],
            [$versions, '4~~', 'v03'],
            [$versions, '4-4', 'v01'],
            [$versions, '4-5', null],
            [$versions, '4+++', 'v08'],
            [$versions, '4+4', 'v09'],
            [$versions, '4+9', null],
            [$versions, '9+', 'v15'],
            [$versions, '9++', null],
            [$versions, '9-3', 'v07'],
            // override operator count
            [$versions, '4---2', 'v03'],
            [$versions, '4~~~2', 'v03'],
            [$versions, '4+++4', 'v09'],
            // test if there's a gap (here between 10 and 15)
            [$versions, '4+6', 'v15'],
            [$versions, '9+', 'v15'],
            [$versions, '9+1', 'v15'],
            [$versions, '10+', null],
            [$versions, '10-', 'v10'],
            [$versions, '10-1', 'v10'],
            // unsupported aliases (by this particular resolver)
            [$versions, null, null],
            [$versions, '', null],
            [$versions, '-3', null],
            [$versions, '+3', null],
            [$versions, '+++', null],
            // no pattern match
            [$versions, '15', null],
            [$versions, 'abc', null],
        ];
    }

    /**
     * testOnlyResolvesIfSortable
     */
    public function testOnlyResolvesIfSortable()
    {
        $instance = new Version\Collection\Resolver\OffsetResolver();
        $collection = new Version\Collection(Version::fromArray(1,2,3));
        $result = $instance->resolve('1+', $collection);
        $this->assertNull($result);
    }
}
