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

namespace Baleen\Migrations\Common\Event\Context;

use Baleen\Migrations\Common\Event\Progress;

/**
 * Class CollectionContext
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
final class CollectionContext implements CollectionContextInterface
{
    /** @var Progress */
    private $progress;

    /**
     * CollectionContext constructor.
     * @param Progress $progress
     */
    public function __construct(Progress $progress)
    {
        $this->progress = $progress;
    }

    /**
     * Returns a Progress object that can indicate the current progress of the run.
     *
     * @return null|\Baleen\Migrations\Common\Event\Progress
     */
    public function getProgress()
    {
        return $this->progress;
    }

    /**
     * Creates a new instance with an instance of Progress created with the specified parameters
     *
     * @param $total
     * @param $current
     *
     * @return static
     */
    public static function createWithProgress($total, $current)
    {
        return new static(new Progress($total, $current));
    }
}
