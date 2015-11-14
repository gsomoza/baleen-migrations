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

namespace Baleen\Migrations\Migration;

use Baleen\Migrations\Exception\InvalidArgumentException;
use Baleen\Migrations\Migration\Options\Direction;
use Baleen\Migrations\Shared\ValueObjectInterface;

/**
 * @{inheritdoc}
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
final class Options implements OptionsInterface
{
    /**
     * @var Direction
     */
    private $direction;

    /**
     * @var bool
     */
    private $forced;

    /**
     * @var bool
     */
    private $dryRun;

    /**
     * @var array
     */
    private $custom;

    /**
     * @var bool
     */
    private $exceptionOnSkip;

    /**
     * @param $direction
     * @param bool $forced
     * @param bool $dryRun
     * @param bool $exceptionOnSkip
     * @param array $custom
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        Direction $direction = null,
        $forced = false,
        $dryRun = false,
        $exceptionOnSkip = true,
        array $custom = []
    ) {
        if (null === $direction) {
            $direction = Direction::up();
        }
        $this->direction = $direction;

        $this->forced = (bool) $forced;
        $this->dryRun = (bool) $dryRun;
        $this->exceptionOnSkip = (bool) $exceptionOnSkip;
        $this->custom = $custom;
    }

    /**
     * getDirection
     *
     * @return Direction
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * @param Direction $direction
     *
     * @return static
     *
     * @throws InvalidArgumentException
     */
    public function withDirection(Direction $direction)
    {
        return new static($direction, $this->forced, $this->dryRun, $this->exceptionOnSkip, $this->custom);
    }

    /**
     * @return bool
     */
    public function isForced()
    {
        return $this->forced;
    }

    /**
     * withForced
     * @param $forced
     * @return static
     */
    public function withForced($forced)
    {
        return new static($this->direction, $forced, $this->dryRun, $this->exceptionOnSkip, $this->custom);
    }

    /**
     * @return bool
     */
    public function isDryRun()
    {
        return $this->dryRun;
    }

    /**
     * withDryRun
     * @param bool $dryRun
     * @return static
     */
    public function withDryRun($dryRun)
    {
        return new static($this->direction, $this->forced, $dryRun, $this->exceptionOnSkip, $this->custom);
    }

    /**
     * @return bool
     */
    public function isExceptionOnSkip()
    {
        return $this->exceptionOnSkip;
    }

    /**
     * @param bool $exceptionOnSkip
     * @return static
     */
    public function withExceptionOnSkip($exceptionOnSkip)
    {
        return new static($this->direction, $this->forced, $this->dryRun, $exceptionOnSkip, $this->custom);
    }

    /**
     * @return array
     */
    public function getCustom()
    {
        return $this->custom;
    }

    /**
     * @param array $custom
     * @return static
     */
    public function withCustom(array $custom)
    {
        return new static($this->direction, $this->forced, $this->dryRun, $this->exceptionOnSkip, $custom);
    }

    /**
     * @inheritdoc
     */
    public function isSameValueAs(ValueObjectInterface $options)
    {
        if (!$options instanceof OptionsInterface) {
            return false;
        }

        return get_class($options) === get_class($this)
            && $this->getDirection()->isSameValueAs($options->getDirection())
            && $this->isForced() === $options->isForced()
            && $this->isDryRun() === $options->isDryRun()
            && $this->isExceptionOnSkip() === $options->isExceptionOnSkip()
            && $this->getCustom() == $options->getCustom();
    }

    /**
     * fromOptionsWithDirection
     *
     * @param Direction $direction
     * @param OptionsInterface|null $options
     *
     * @return static
     */
    public static function fromOptionsWithDirection(Direction $direction, OptionsInterface $options = null)
    {
        if (null === $options) {
            $options = (new static($direction))->withExceptionOnSkip(false);
        } else {
            $options = $options->withDirection($direction);
        }
        return $options;
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function __toString()
    {
        return __CLASS__ . '@' . spl_object_hash($this);
    }
}
