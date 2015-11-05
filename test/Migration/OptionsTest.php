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
 * <https://github.com/baleen/migrations>.
 */

namespace BaleenTest\Migrations\Migration\Factory;

use Baleen\Migrations\Exception\InvalidArgumentException;
use Baleen\Migrations\Migration\Options;
use Baleen\Migrations\Migration\OptionsInterface;
use BaleenTest\Migrations\BaseTestCase;
use Mockery as m;

/**
 * Class OptionsTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class OptionsTest extends BaseTestCase
{
    /**
     * testConstructor
     */
    public function testConstructor()
    {
        $direction = Options::DIRECTION_DOWN;
        $forced = true;
        $dryRun = true;
        $exceptionOnSkip = false;
        $custom = ['foo' => 'bar'];

        $instance = new Options($direction, $forced, $dryRun, $exceptionOnSkip, $custom);
        $this->assertTrue($instance->isDirectionDown(), "Expected option's direction to be down.");
        $this->assertSame($instance->isForced(), $forced);
        $this->assertSame($instance->isDryRun(), $dryRun);
        $this->assertSame($instance->isExceptionOnSkip(), $exceptionOnSkip);
        $this->assertSame($instance->getCustom(), $custom);
    }

    /**
     * testConstructorDefaults
     */
    public function testConstructorDefaults()
    {
        $instance = new Options(Options::DIRECTION_UP);
        $this->assertSame($instance->isForced(), false);
        $this->assertSame($instance->isDryRun(), false);
        $this->assertSame($instance->isExceptionOnSkip(), true);
        $this->assertSame($instance->getCustom(), []);
    }

    /**
     * testWithDirectionExceptionIfNotAllowed
     */
    public function testWithDirectionExceptionIfNotAllowed()
    {
        $instance = new Options(Options::DIRECTION_UP);

        $this->setExpectedException(InvalidArgumentException::class);
        $instance->withDirection('foo');
    }

    /**
     * testIsDirectionDown
     */
    public function testIsDirectionDown()
    {
        $instance = new Options(Options::DIRECTION_DOWN);
        $this->assertTrue($instance->isDirectionDown());
        $this->assertFalse($instance->isDirectionUp());
    }

    /**
     * testWithForced
     */
    public function testWithForced()
    {
        $options = new Options();
        $this->assertFalse($options->isForced());
        $this->assertTrue($options->withForced(true)->isForced());
    }

    /**
     * testWithDryRun
     */
    public function testWithDryRun()
    {
        $options = new Options();
        $this->assertFalse($options->isDryRun());
        $this->assertTrue($options->withDryRun(true)->isDryRun());
    }

    /**
     * testWithExceptionOnSkip
     */
    public function testWithExceptionOnSkip()
    {
        $options = new Options();
        $this->assertTrue($options->isExceptionOnSkip());
        $this->assertFalse($options->withExceptionOnSkip(false)->isExceptionOnSkip());
    }

    /**
     * testWithCustom
     */
    public function testWithCustom()
    {
        $options = new Options();
        $this->assertEmpty($options->getCustom());
        $this->assertNotEmpty($options->withCustom(['test'])->getCustom());
    }

    /**
     * testEquals
     * @param OptionsInterface $options2
     *
     * @dataProvider equalsProvider
     */
    public function testEquals(OptionsInterface $options2, $expected)
    {
        $options = new Options();
        $this->assertEquals($expected, $options->equals($options2));
    }

    /**
     * equalsProvider
     * @return array
     */
    public function equalsProvider()
    {
        $baseOptions = new Options();
        return [
            [$baseOptions, true], // same
            [m::mock(OptionsInterface::class), false], // another class
            [$baseOptions->withCustom(['test']), false], // different custom
            [$baseOptions->withDirection(OptionsInterface::DIRECTION_DOWN), false], // different direction
            [$baseOptions->withDryRun(!$baseOptions->isDryRun()), false], // different dry-run
            [$baseOptions->withDryRun(!$baseOptions->isDryRun()), false], // different dry-run
            [$baseOptions->withExceptionOnSkip(!$baseOptions->isExceptionOnSkip()), false], // different exception on skip
        ];
    }
}
