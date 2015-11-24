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

namespace Baleen\Migrations\Service\Command\Migrate\Single;

use Baleen\Migrations\Migration\OptionsInterface;
use Baleen\Migrations\Service\Command\Migrate\AbstractMigrateCommand;
use Baleen\Migrations\Shared\Event\Context\CollectionContext;
use Baleen\Migrations\Shared\Event\Context\CollectionContextInterface;
use Baleen\Migrations\Shared\Event\Progress;
use Baleen\Migrations\Version\Repository\VersionRepository;
use Baleen\Migrations\Version\Repository\VersionRepositoryInterface;
use Baleen\Migrations\Version\VersionInterface;

/**
 * Class SingleCommand
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
final class SingleCommand extends AbstractMigrateCommand
{
    /** @var CollectionContextInterface */
    private $context;

    /**
     * CollectionCommand constructor.
     *
     * @param VersionInterface $target
     * @param OptionsInterface $options
     * @param VersionRepositoryInterface $versionRepository
     * @param CollectionContextInterface $context
     */
    public function __construct(
        VersionInterface $target,
        OptionsInterface $options,
        VersionRepositoryInterface $versionRepository,
        CollectionContextInterface $context = null
    ) {
        if (null === $context) {
            $context = new CollectionContext(new Progress(1, 1));
        }
        $this->context = $context;

        parent::__construct($target, $options, $versionRepository);
    }

    /**
     * @return CollectionContextInterface
     */
    public function getContext()
    {
        return $this->context;
    }
}
