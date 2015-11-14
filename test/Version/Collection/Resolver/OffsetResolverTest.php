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

use Baleen\Migrations\Version\Collection\Collection;
use Baleen\Migrations\Version\Collection\Resolver\OffsetResolver;
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
        $collection = new Collection($versions, $instance);
        $result = $instance->resolve($alias, $collection);
        $actual = $result ? (string) $result->getId() : null;
        $this->assertEquals($expected, $actual);
    }

    /**
     * resolveProvider
     */
    public function resolveProvider()
    {
        $versions = $this->buildVersions(['v01','v02','v03','v04','v05','v06','v07','v08','v09','v10','v15']);
        return [
            [$versions, 'v01+', 'v02'],
            [$versions, 'v01+++', 'v04'],
            [$versions, 'v01+2', 'v03'],
            [$versions, 'v01-', null],
            [$versions, 'v04-', 'v03'],
            [$versions, 'v04~', 'v03'],
            [$versions, 'v04--', 'v02'],
            [$versions, 'v04~~', 'v02'],
            [$versions, 'v04-4', null], // there's nothing at getPosition 0
            [$versions, 'v04-5', null], // there's nothing at getPosition -1
            [$versions, 'v04+++', 'v07'],
            [$versions, 'v04+4', 'v08'],
            [$versions, 'v04+9', null], // there's nothing at getPosition 13
            [$versions, 'v09+', 'v10'],
            [$versions, 'v09-3', 'v06'],
            // override operator count
            [$versions, 'v04---2', 'v02'],
            [$versions, 'v04~~~2', 'v02'],
            [$versions, 'v04+++4', 'v08'],
            // test if there's a gap (here between 10 and 15)
            [$versions, 'v09++', 'v15'], // version v15 is at getPosition 11
            [$versions, 'v04+7', 'v15'],
            [$versions, 'v10+', 'v15'],
            [$versions, 'v10+1', 'v15'],
            [$versions, 'v10+2', null],
            [$versions, 'v15-', 'v10'],
            [$versions, 'v15-1', 'v10'],
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
        $instance = new OffsetResolver();
        $collection = new Collection($this->buildVersions(range(1, 3)));
        $result = $instance->resolve('1+', $collection);
        $this->assertNull($result);
    }
}
