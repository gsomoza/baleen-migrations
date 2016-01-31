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

namespace BaleenTest\Migrations\Migration\Options;

use Baleen\Migrations\Exception\InvalidArgumentException;
use Baleen\Migrations\Migration\Options\Direction;
use Baleen\Migrations\Delta\DeltaId;
use BaleenTest\Migrations\BaseTestCase;
use Mockery as m;

/**
 * Class DirectionTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class DirectionTest extends BaseTestCase
{
    /**
     * testWithDirectionExceptionIfNotAllowed
     */
    public function testConstructorInvalidArgument()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        new Direction('foo');
    }

    /**
     * testConstructorInteger
     * @return void
     */
    public function testConstructorInteger()
    {
        $this->assertTrue((new Direction(1))->isUp());
        $this->assertTrue((new Direction(-1))->isDown());
    }

    /**
     * testIsSameValueAs
     * @return void
     */
    public function testIsSameValueAs()
    {
        $d1 = new Direction(1);
        $d2 = new Direction(2);
        $d3 = new Direction(-1);
        $id = new DeltaId('v1');

        $this->assertTrue($d1->isSameValueAs($d2));
        $this->assertFalse($d2->isSameValueAs($d3));
        $this->assertFalse($d3->isSameValueAs($d1));
        $this->assertFalse($d1->isSameValueAs($id));
    }

    /**
     * testToString
     * @return void
     */
    public function testToString()
    {
        $up = Direction::up();
        $down = Direction::down();
        $this->assertSame(Direction::UP, (string) $up);
        $this->assertSame(Direction::DOWN, (string) $down);
    }
}
