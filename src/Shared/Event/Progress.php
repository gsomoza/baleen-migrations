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

namespace Baleen\Migrations\Shared\Event;

use Baleen\Migrations\Exception\InvalidArgumentException;

/**
 * Class Progress.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
final class Progress
{
    /** @var int */
    private $total;

    /** @var int */
    private $current;

    /**
     * Progress constructor.
     *
     * @param int $total
     * @param int $current
     *
     * @throws InvalidArgumentException
     */
    public function __construct($total, $current)
    {
        if (!is_numeric($total) || (int) $total <= 0) {
            throw new InvalidArgumentException('Argument "total" must be an integer greater than zero.');
        }
        $this->total = (int) $total;
        $this->update($current);
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return int
     */
    public function getCurrent()
    {
        return $this->current;
    }

    /**
     * Update the current progress
     *
     * @param integer $newProgress
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    public function update($newProgress) {
        if (!is_numeric($newProgress) || (int) $newProgress < 0 || (int) $newProgress > $this->total) {
            throw new InvalidArgumentException(sprintf(
                'Argument must be an integer between 0 (zero) and %s (total).',
                $this->total
            ));
        }
        $this->current = $newProgress;
    }
}
