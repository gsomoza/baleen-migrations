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

namespace BaleenTest\Migrations\Version\Comparator;

use Baleen\Migrations\Exception\Version\ComparatorException;
use Baleen\Migrations\Migration\Options\Direction;
use Baleen\Migrations\Version\Comparator\ComparatorInterface;
use Baleen\Migrations\Version\Comparator\NamespacesAwareComparator;
use Mockery as m;

/**
 * Class NamespacesAwareComparatorTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class NamespacesAwareComparatorTest extends ComparatorTestCase
{
    const NS_VALID = 'BaleenTest\\Migrations\\Fixtures\\Migrations\\AllValid\\';

    /**
     * testConstructor
     */
    public function testConstructor()
    {
        /** @var ComparatorInterface $fallback */
        $fallback = m::mock(ComparatorInterface::class);
        $instance = new NamespacesAwareComparator($fallback, ['test']);
        $this->assertInstanceOf(ComparatorInterface::class, $instance);
    }

    /**
     * testConstructorEmptyNamespaces
     */
    public function testConstructorEmptyNamespaces()
    {
        /** @var ComparatorInterface $fallback */
        $fallback = m::mock(ComparatorInterface::class);
        $this->setExpectedException(ComparatorException::class);
        new NamespacesAwareComparator($fallback, []);
    }

    /**
     * testConstructorOrder
     *
     * @param $direction
     * @param $expected
     * @dataProvider constructorDirectionProvider
     */
    public function testConstructorDirection($direction, $expected)
    {
        /** @var ComparatorInterface $fallback */
        $fallback = m::mock(ComparatorInterface::class);
        // $v1 is greater than $v2 here because its namespace appears in the comparator
        $comparator = new NamespacesAwareComparator($fallback, [self::NS_VALID]);
        /** @var Direction $direction */
        if (null !== $direction && $direction->isDown()) {
            $comparator = $comparator->getReverse();
        }
        $result = $this->simpleCompare($comparator);
        $this->assertEquals($expected, $result);
    }

    /**
     * constructorDirectionProvider
     * @return array
     */
    public function constructorDirectionProvider()
    {
        // $v1 is greater than $v2 here because its namespace appears in the comparator
        return [
            // normal order
            [null, 1],
            [Direction::up(), 1],
            // reverse order
            [Direction::down(), -1],
        ];
    }

    /**
     * testWithDirection
     */
    public function testWithDirection()
    {
        /** @var ComparatorInterface $fallback */
        $fallback = m::mock(ComparatorInterface::class);
        // $v1 is greater than $v2 here because its namespace appears in the comparator
        $comparator = new NamespacesAwareComparator($fallback, [self::NS_VALID]);
        // but we're reversing the comparator
        $comparator = $comparator->getReverse();
        $result = $this->simpleCompare($comparator);
        // so we expect the result to be negative
        $this->assertEquals(-1, $result);
    }

    /**
     * testCompare
     * @param $namespaces
     * @param string[] $migration1
     * @param string[] $migration2
     * @param int $expected
     * @dataProvider compareProvider
     */
    public function testCompare($namespaces, $migration1, $migration2, $expected)
    {
        $m1 = $this->createMigration($migration1[0], $migration1[1]);
        $v1 = $this->buildVersion(null, false, $m1);
        $m2 = $this->createMigration($migration2[0], $migration2[1]);
        $v2 = $this->buildVersion(null, false, $m2);

        /** @var ComparatorInterface|m\Mock $fallback */
        $fallback = m::mock(ComparatorInterface::class);
        $comparator = new NamespacesAwareComparator($fallback, $namespaces);

        if ($expected === 'fallback') {
            $fallback->shouldReceive('__invoke')->once()->andReturn(-1);
            $expected = -1;
        }

        $result = $comparator($v1, $v2);
        $this->assertEquals($expected, $result);
    }

    /**
     * compareProvider
     * @return array
     */
    public function compareProvider()
    {
        return [
            // identical classes
            [ ['Xyz'], ['Abc', 'Version'], ['Abc', 'Version'], 0 ],
            [ ['Xyz'], ['Xyz', 'Version'], ['Xyz', 'Version'], 0 ],
            // only one namespace
            [ ['Xyz'], ['Abc', 'Version'], ['Xyz', 'Version'], -1 ],
            [ ['Abc'], ['Abc', 'Version'], ['Xyz', 'Version'], 1 ],
            // namespace order (first is higher priority)
            [ ['Xyz', 'Abc'], ['Abc', 'Version'], ['Xyz', 'Version'], 1 ],
            [ ['Abc', 'Xyz'], ['Abc', 'Version'], ['Xyz', 'Version'], -1 ],
            // one unused namespace => should use fallback
            [ ['Klm'], ['Abc', 'Version'], ['Xyz', 'Version'], 'fallback' ],
            [ ['Klm'], ['Xyz', 'Version'], ['Abc', 'Version'], 'fallback' ],
        ];
    }
}
