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
        $versions = Version::fromArray('01','02','03','04','05','06','07','08','09','10','15');
        return [
            [[], '0', null],
            [[], '1', null],
            [[], '1+', null],
            [$versions, '0-3', null],
            [$versions, '0', null],
            [$versions, '0+2', null],
            [$versions, '01+', '02'],
            [$versions, '01++', '03'],
            [$versions, '01+2', '03'],
            [$versions, '01-', null],
            [$versions, '05-', '04'],
            [$versions, '05~', '04'],
            [$versions, '05--', '03'],
            [$versions, '05~~', '03'],
            [$versions, '05-4', '01'],
            [$versions, '05-5', null],
            [$versions, '05+++', '08'],
            [$versions, '05+4', '09'],
            [$versions, '05+9', null],
            [$versions, '10+', '15'],
            [$versions, '10++', null],
            [$versions, '10-3', '7'],
            // override operator count
            [$versions, '05---2', '03'],
            [$versions, '05~~~2', '03'],
            [$versions, '05+++4', '09'],
            // test if there's a gap (here between 10 and 15)
            [$versions, '05+6', '15'],
            [$versions, '10+', '15'],
            [$versions, '10+1', '15'],
            [$versions, '15+', null],
            [$versions, '15-', '10'],
            [$versions, '15-1', '10'],
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
