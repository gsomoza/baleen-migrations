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

use Baleen\Migrations\Exception\Version\Collection\ResolverException;
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Delta\Collection\Collection;
use Baleen\Migrations\Delta\Collection\Resolver\AbstractResolver;
use Baleen\Migrations\Delta\DeltaInterface;
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
        $instance->resolve('HEAD', new Collection());
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

    /**
     * testResolve
     * @param $alias
     * @param $resolvedResult
     * @throws ResolverException
     * @dataProvider resolveWithoutCacheProvider
     */
    public function testResolveWithCache($alias, $resolvedResult)
    {
        /** @var Collection|m\Mock $collection */
        $collection = m::mock(Collection::class);
        /** @var AbstractResolver|m\Mock $instance */
        $instance = m::mock(AbstractResolver::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
        $instance->shouldReceive('doResolve')->once()->with((string) $alias, $collection)->andReturn($resolvedResult);
        $result = $instance->resolve($alias, $collection);
        $this->assertEquals($resolvedResult, $result);

        // test that the value was cached and can be retrieved from the cache
        $cached = $instance->resolve($alias, $collection);
        // it would blow up here if it tried to 'doResolve' again
        $this->assertEquals($resolvedResult, $cached);
    }

    /**
     * resolveWithoutCacheProvider
     * @return array
     */
    public function resolveWithoutCacheProvider()
    {
        $v = m::mock(DeltaInterface::class);
        /** @var MigrationInterface|m\Mock $migration */
        $migration = m::mock(MigrationInterface::class);
        return [
            ['v1', $v],
            ['v2', null],
            [$this->buildVersion('v1', false, $migration), $v],
        ];
    }

    /**
     * testClearCache
     */
    public function testClearCache()
    {
        $alias = '123';
        /** @var DeltaInterface|m\Mock $version */
        $version = m::mock(DeltaInterface::class);
        /** @var Collection|m\Mock $collection */
        $collection = m::mock(Collection::class);
        /** @var AbstractResolver|m\Mock $instance */
        $instance = m::mock(AbstractResolver::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
        $instance->shouldReceive('doResolve')->zeroOrMoreTimes()->with($alias, $collection)->andReturn($version);

        // warm up the cache for this alias
        $result = $instance->resolve($alias, $collection);
        $instance->shouldHaveReceived('doResolve')->once();
        $this->assertEquals($version, $result);

        // test that the value was cached and can be retrieved from the cache
        $cached = $instance->resolve($alias, $collection);
        $instance->shouldHaveReceived('doResolve')->once();
        $this->assertEquals($version, $cached);

        // clear cache
        $instance->clearCache();

        // this should not hit the cache
        $result = $instance->resolve($alias, $collection);
        $instance->shouldHaveReceived('doResolve')->twice();
        $this->assertEquals($version, $result);
    }

    /**
     * testClearCollectionCache
     * @throws ResolverException
     */
    public function testClearCollectionCache()
    {
        $alias = '123';
        /** @var DeltaInterface|m\Mock $version */
        $version = m::mock(DeltaInterface::class);
        /** @var Collection|m\Mock $collection1 */
        $collection1 = m::mock(Collection::class);
        /** @var Collection|m\Mock $collection2 */
        $collection2 = m::mock(Collection::class);

        /** @var AbstractResolver|m\Mock $instance */
        $instance = m::mock(AbstractResolver::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
        $instance->shouldReceive('doResolve')
            ->zeroOrMoreTimes()
            ->with($alias, m::type(Collection::class))
            ->andReturn($version);

        // warm up the cache for collection 1
        $result = $instance->resolve($alias, $collection1);
        $instance->shouldHaveReceived('doResolve')->once();
        $this->assertEquals($version, $result);

        // test that the value was cached and can be retrieved from the cache
        $cached = $instance->resolve($alias, $collection1);
        $instance->shouldHaveReceived('doResolve')->once();
        $this->assertEquals($version, $cached);

        // warm up the cache for collection 2
        $result = $instance->resolve($alias, $collection2);
        $instance->shouldHaveReceived('doResolve')->twice();
        $this->assertEquals($version, $result);

        // test that the value was cached and can be retrieved from the cache
        $cached = $instance->resolve($alias, $collection2);
        $instance->shouldHaveReceived('doResolve')->twice();
        $this->assertEquals($version, $cached);

        // clear cache for collection 1
        $instance->clearCache($collection1);

        // collection 1 should not hit the cache
        $result = $instance->resolve($alias, $collection1);
        $instance->shouldHaveReceived('doResolve')->times(3);
        $this->assertEquals($version, $result);

        // but collection 2 should
        $result = $instance->resolve($alias, $collection2);
        $instance->shouldHaveReceived('doResolve')->times(3);
        $this->assertEquals($version, $result);
    }
}
