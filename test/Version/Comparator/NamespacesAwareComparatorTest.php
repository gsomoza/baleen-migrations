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

use Baleen\Migrations\Exception\InvalidArgumentException;
use Baleen\Migrations\Exception\Version\ComparatorException;
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version\Comparator\ComparatorInterface;
use Baleen\Migrations\Version\Comparator\NamespacesAwareComparator;
use Mockery as m;

/**
 * Class NamespacesAwareComparatorTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class NamespacesAwareComparatorTest extends ComparatorTestCase
{
    const NS_VALID = 'BaleenTest\\Migrations\\Migrations\\AllValid\\';

    /**
     * testConstructor
     */
    public function testConstructor()
    {
        /** @var ComparatorInterface $fallback */
        $fallback = m::mock(ComparatorInterface::class);
        $instance = new NamespacesAwareComparator(1, $fallback, ['test']);
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
        new NamespacesAwareComparator(1, $fallback, []);
    }

    /**
     * testConstructorOrder
     *
     * @param $order
     * @param $expected
     * @dataProvider constructorOrderProvider
     */
    public function testConstructorOrder($order, $expected)
    {
        /** @var ComparatorInterface $fallback */
        $fallback = m::mock(ComparatorInterface::class);
        // $v1 is greater than $v2 here because its namespace appears in the comparator
        $comparator = new NamespacesAwareComparator($order, $fallback, [self::NS_VALID]);
        $result = $this->simpleCompare($comparator);
        $this->assertEquals($expected, $result);
    }

    /**
     * constructorOrderProvider
     * @return array
     */
    public function constructorOrderProvider()
    {
        // $v1 is greater than $v2 here because its namespace appears in the comparator
        return [
            // normal order
            [null, 1],
            [0, 1],
            [1, 1],
            [10, 1],
            ['10', 1],
            // reverse order
            [-1, -1],
            ['-1', -1],
        ];
    }

    /**
     * testWithOrder
     */
    public function testWithOrder()
    {
        /** @var ComparatorInterface $fallback */
        $fallback = m::mock(ComparatorInterface::class);
        // $v1 is greater than $v2 here because its namespace appears in the comparator
        $comparator = new NamespacesAwareComparator(1, $fallback, [self::NS_VALID]);
        // but we're reversing the comparator
        $comparator = $comparator->withOrder(-1);
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
        $v1 = new Version(spl_object_hash($m1), false, $m1);
        $m2 = $this->createMigration($migration2[0], $migration2[1]);
        $v2 = new Version(spl_object_hash($m2), false, $m2);

        /** @var ComparatorInterface|m\Mock $fallback */
        $fallback = m::mock(ComparatorInterface::class);
        $comparator = new NamespacesAwareComparator(1, $fallback, $namespaces);

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

    /**
     * testCompareThrowsExceptionIfNoMigration
     * @param $withMigration1
     * @param $withMigration2
     * @dataProvider compareThrowsExceptionIfNoMigrationProvider
     */
    public function testCompareThrowsExceptionIfNoMigration($withMigration1, $withMigration2)
    {
        $v1 = new Version('abc', false, $withMigration1 ? m::mock(MigrationInterface::class) : null);
        $v2 = new Version('xyz', false, $withMigration2 ? m::mock(MigrationInterface::class) : null);
        /** @var ComparatorInterface|m\Mock $fallback */
        $fallback = m::mock(ComparatorInterface::class);
        $comparator = new NamespacesAwareComparator(1, $fallback, ['test']);
        $this->setExpectedException(InvalidArgumentException::class);
        $comparator($v1, $v2);
    }

    /**
     * twoTrueFalseProvider
     * @return array
     */
    public function compareThrowsExceptionIfNoMigrationProvider()
    {
        return [
            [true, false],
            [false, true],
        ];
    }
}
