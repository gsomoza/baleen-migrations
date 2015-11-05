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
namespace Baleen\Migrations\Migration;

use Baleen\Migrations\Exception\InvalidArgumentException;


/**
 * Options value object. Used to configure the migration jobs and provide information about them to the migration.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
interface OptionsInterface
{
    const DIRECTION_UP = 'up';
    const DIRECTION_DOWN = 'down';

    /**
     * The direction that we're migrating
     *
     * @return string
     */
    public function getDirection();

    /**
     * MUST return a new OptionsInterface instance with the same property values as the current one except for the new
     * direction.
     *
     * @param string $direction
     *
     * @return static
     *
     * @throws InvalidArgumentException
     */
    public function withDirection($direction);

    /**
     * @return bool
     */
    public function isDirectionUp();

    /**
     * @return bool
     */
    public function isDirectionDown();

    /**
     * @return bool
     */
    public function isForced();

    /**
     * MUST return a new OptionsInterface instance with the same property values as the current one except for the new
     * value for the "forced" property.
     *
     * @param $forced
     *
     * @return static
     */
    public function withForced($forced);

    /**
     * @return bool
     */
    public function isDryRun();

    /**
     * MUST return a new OptionsInterface instance with the same property values as the current one except for the new
     * value for the "dryRun" property.
     *
     * @param bool $dryRun
     * @return static
     */
    public function withDryRun($dryRun);

    /**
     * @return bool
     */
    public function isExceptionOnSkip();

    /**
     * MUST return a new OptionsInterface instance with the same property values as the current one except for the new
     * value for the "exceptionOnSkip" property.
     *
     * @param bool $exceptionOnSkip
     * @return static
     */
    public function withExceptionOnSkip($exceptionOnSkip);

    /**
     * @return array
     */
    public function getCustom();

    /**
     * MUST return a new OptionsInterface instance with the same property values as the current one except for the new
     * value for the "custom" array.
     *
     * @param array $custom
     * @return static
     */
    public function withCustom(array $custom);

    /**
     * Returns true if the current object is the same as the parameter.
     * @param OptionsInterface $options
     * @return boolean
     */
    public function equals(OptionsInterface $options);
}
