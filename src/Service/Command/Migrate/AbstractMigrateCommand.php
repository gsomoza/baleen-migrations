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

namespace Baleen\Migrations\Service\Command\Migrate;

use Baleen\Migrations\Migration\Options;
use Baleen\Migrations\Migration\OptionsInterface;
use Baleen\Migrations\Version\VersionInterface;

/**
 * Class AbstractMigrateCommand
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
abstract class AbstractMigrateCommand
{
    /** @var VersionInterface */
    private $target;

    /** @var OptionsInterface */
    private $options;

    /**
     * CollectionCommand constructor.
     *
     * @param VersionInterface $target
     * @param OptionsInterface $options
     */
    public function __construct(VersionInterface $target, OptionsInterface $options)
    {
        $this->target = $target;
        $this->options = $options;
    }

    /**
     * @return VersionInterface
     */
    final public function getTarget()
    {
        return $this->target;
    }

    /**
     * @return OptionsInterface
     */
    final public function getOptions()
    {
        return $this->options;
    }
}
