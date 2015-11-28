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

namespace Baleen\Migrations\Migration\Options;

use Baleen\Migrations\Exception\InvalidArgumentException;
use Baleen\Migrations\Shared\ValueObjectInterface;

/**
 * Class Direction
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
final class Direction implements ValueObjectInterface
{
    const UP = 'up';
    const DOWN = 'down';

    /** @var string */
    private $direction;

    /**
     * Direction constructor.
     * @param int|string $direction
     * @throws InvalidArgumentException
     */
    public function __construct($direction)
    {
        if (is_integer($direction)) {
            $direction = (int) $direction < 0 ? self::DOWN : self::UP;
        }
        $direction = (string) $direction;
        if (!in_array($direction, [self::UP, self::DOWN])) {
            throw new InvalidArgumentException(
                sprintf('Unknown direction "%s". Valid options are "up" or "down".', $direction)
            );
        }
        $this->direction = $direction;
    }

    /**
     * Returns whether the direction is up
     *
     * @return bool
     */
    public function isUp() {
        return $this->direction === self::UP;
    }

    /**
     * Returns whether the direction is down
     *
     * @return bool
     */
    public function isDown() {
        return !$this->isUp();
    }

    /**
     * Returns the direction as string
     *
     * @return string
     */
    public function getDirection() {
        return $this->direction;
    }

    /**
     * @inheritdoc
     */
    public function isSameValueAs(ValueObjectInterface $object)
    {
        if (!$object instanceof Direction) {
            return false;
        }

        return $this->isUp() == $object->isUp();
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getDirection();
    }

    /**
     * Returns an instance with direction UP
     *
     * @return static
     */
    public static function up()
    {
        return new static(static::UP);
    }

    /**
     * Returns an instance with direction DOWN
     *
     * @return static
     */
    public static function down()
    {
        return new static(static::DOWN);
    }
}
