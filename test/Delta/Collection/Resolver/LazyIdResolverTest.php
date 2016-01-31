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
use Baleen\Migrations\Delta\Collection\Resolver\LazyIdResolver;
use BaleenTest\Migrations\BaseTestCase;
use Mockery as m;

/**
 * Class LazyIdResolverTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class LazyIdResolverTest extends BaseTestCase
{
    /**
     * testResolve
     *
     * @param array $versions
     * @param $alias
     * @param $expectedId
     *
     * @dataProvider indexOfIdProvider
     */
    public function testResolve(array $versions, $alias, $expectedId) {
        $collection = new Collection($this->buildVersions($versions));
        $resolver = new LazyIdResolver();
        $result = $resolver->resolve($alias, $collection);
        $this->assertEquals($expectedId, (string) $result);
    }

    /**
     * indexOfIdProvider
     */
    public function indexOfIdProvider()
    {
        $col1 = range(1,10);
        $col2 = ['abcd1234', 'abcd9876', '1234abcd', '9876xyz'];
        $col3 = ['abcd1234', 'abcd1876', 'xyz1', 'xyz'];
        return [
            // direct matches
            [$col1, 'v1', 'v1'],
            [$col1, $this->buildVersion(2), 'v2'], // converts parameter to string
            [$col1, 'v10', 'v10'],
            [$col1, 'v11', null],
            // lazy matches
            [$col1, 'v', null],
            [$col2, 'abcd', null],
            [$col2, 'abcd1', 'abcd1234'],
            [$col2, 'abcd9', 'abcd9876'],
            [$col2, '1', '1234abcd'],
            [$col2, '9', '9876xyz'],
            [$col2, '9876xyB', null],
            [$col2, 'cd12', null], // middle of the string
            [$col2, 'xyz', null], // should not match end of the string
            [$col3, 'xyz', 'xyz'], // exact version match takes precedence over lazy search
            // lazy aborts
            [$col3, 'abcd1', null], // test that candidate is reset to null if more than 1 candidate
        ];
    }
}
