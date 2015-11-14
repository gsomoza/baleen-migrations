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

namespace BaleenTest\Migrations\Shared\Event;

use Baleen\Migrations\Exception\InvalidArgumentException;
use Baleen\Migrations\Shared\Event\Progress;
use BaleenTest\Migrations\BaseTestCase;
use Mockery as m;

/**
 * Class ProgressTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class ProgressTest extends BaseTestCase
{

    /**
     * testConstructor
     * @param $total
     * @param $current
     * @dataProvider constructorProvider
     */
    public function testConstructor($total, $current)
    {
        if ($total < 1) {
            $this->setExpectedException(InvalidArgumentException::class, 'must be an integer greater than zero');
        } elseif ($current < 0 || $current > $total) {
            $this->setExpectedException(
                InvalidArgumentException::class,
                sprintf('must be an integer between 0 (zero) and %s', $total)
            );
        }
        $instance = new Progress($total, $current);
        $this->assertEquals($total, $instance->getTotal());
        $this->assertEquals($current, $instance->getCurrent());
    }

    /**
     * constructorProvider
     * @return array
     */
    public function constructorProvider()
    {
        $validTotals = [1, 999999, '99'];
        $validCurrents = [0, 1, 999999, '99'];
        $cases = $this->combinations([$validCurrents, $validTotals]);
        $cases[] = [-1, 0]; // invalid total
        $cases[] = [0, 0];  // invalid total
        $cases[] = [1, -1]; // invalid current
        return $cases;
    }

    /**
     * testSetCurrent
     * @throws InvalidArgumentException
     */
    public function testSetCurrent()
    {
        $instance = new Progress(10, 2);
        $this->assertEquals(2, $instance->getCurrent());
        $instance->update(9);
        $this->assertEquals(9, $instance->getCurrent());
    }
}
