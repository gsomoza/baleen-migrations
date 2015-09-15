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

use Baleen\Migrations\Exception\ResolverException;
use Baleen\Migrations\Version\Collection\IndexedVersions;
use Baleen\Migrations\Version\Collection\Resolver\AbstractResolver;
use BaleenTest\Migrations\BaseTestCase;
use Mockery as m;

/**
 * Class AbstractResolverTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class AbstractResolverTest extends BaseTestCase
{

    /**
     * testResolveThrowsException
     * @param $returnType
     *
     * @dataProvider returnTypeProvider
     */
    public function testResolveChecksReturnType($returnType)
    {
        /** @var m\Mock|AbstractResolver $instance */
        $instance = m::mock(AbstractResolver::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
        $instance->shouldReceive('doResolve')->once()->andReturn($returnType);
        $this->setExpectedException(ResolverException::class);
        $instance->resolve('HEAD', new IndexedVersions());
    }

    /**
     * returnTypeProvider
     * @return array
     */
    public function returnTypeProvider()
    {
        return [
            ['123'],
            [123],
            [1.2],
            [new \stdClass()],
        ];
    }
}
