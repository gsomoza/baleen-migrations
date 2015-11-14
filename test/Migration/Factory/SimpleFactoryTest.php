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
use Baleen\Migrations\Migration\Factory\SimpleFactory;
use Baleen\Migrations\Migration\Options;
use Baleen\Migrations\Migration\Options\Direction;
use Baleen\Migrations\Migration\OptionsInterface;
use BaleenTest\Migrations\BaseTestCase;

/**
 * Class SimpleFactoryTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class SimpleFactoryTest extends BaseTestCase
{
    /**
     * testCreateSimple
     * @throws InvalidArgumentException
     */
    public function testCreateSimple()
    {
        $instance = new SimpleFactory();
        $result = $instance->create(\stdClass::class);
        $this->assertInstanceOf(\stdClass::class, $result);
    }

    /**
     * testCreateException
     * @throws InvalidArgumentException
     */
    public function testCreateExceptionWithInvalidClass()
    {
        $instance = new SimpleFactory();
        $this->setExpectedException(InvalidArgumentException::class);
        $instance->create('');
    }

    /**
     * testCreateExceptionWithInvalidArgs
     * @throws InvalidArgumentException
     */
    public function testCreateExceptionWithInvalidArgs()
    {
        $factory = new SimpleFactory();
        $this->setExpectedException(InvalidArgumentException::class);
        $factory->create(Options::class, Direction::down()); // no array!
    }

    /**
     * testCreateWithArgs
     */
    public function testCreateWithArgs()
    {
        $factory = new SimpleFactory();
        /** @var OptionsInterface $result */
        $result = $factory->create(Options::class, [Direction::down()]);
        $this->assertInstanceOf(Options::class, $result);
        $this->assertTrue($result->getDirection()->isDown());
    }
}
