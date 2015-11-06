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
     * @var array
     */
    private $allowedDirections;

    /**
     * @var string
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
        $direction = self::DIRECTION_UP,
        $forced = false,
        $dryRun = false,
        $exceptionOnSkip = true,
        array $custom = []
    ) {
        $this->allowedDirections = [
            self::DIRECTION_UP,
            self::DIRECTION_DOWN,
        ];
        $this->setDirection($direction);
        $this->forced = (bool) $forced;
        $this->dryRun = (bool) $dryRun;
        $this->exceptionOnSkip = (bool) $exceptionOnSkip;
        $this->custom = $custom;
    }

    /**
     * setDirection
     * @param $direction
     * @throws InvalidArgumentException
     */
    private function setDirection($direction)
    {
        if (!in_array($direction, $this->allowedDirections)) {
            throw new InvalidArgumentException(
                sprintf('Unknown direction "%s". Valid options are "up" or "down".', $direction)
            );
        }
        $this->direction = $direction;
    }

    /**
     * getDirection
     * @return string
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * @param string $direction
     *
     * @return static
     *
     * @throws InvalidArgumentException
     */
    public function withDirection($direction)
    {
        return new static($direction, $this->forced, $this->dryRun, $this->exceptionOnSkip, $this->custom);
    }

    /**
     * @return bool
     */
    public function isDirectionUp()
    {
        return $this->direction == self::DIRECTION_UP;
    }

    /**
     * @return bool
     */
    public function isDirectionDown()
    {
        return $this->direction == self::DIRECTION_DOWN;
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
     * Compares the current instance with another instance of options to see if they contain the same values.
     *
     * @param OptionsInterface $options
     * @return bool
     */
    public function equals(OptionsInterface $options)
    {
        return get_class($options) === get_class($this)
            && $this->getDirection() === $options->getDirection()
            && $this->isForced() === $options->isForced()
            && $this->isDryRun() === $options->isDryRun()
            && $this->isExceptionOnSkip() === $options->isExceptionOnSkip()
            && $this->getCustom() == $options->getCustom();
    }
}
