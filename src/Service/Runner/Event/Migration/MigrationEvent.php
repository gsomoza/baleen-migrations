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

namespace Baleen\Migrations\Service\Runner\Event\Migration;

use Baleen\Migrations\Migration\OptionsInterface;
use Baleen\Migrations\Common\Event\AbstractDomainEvent;
use Baleen\Migrations\Common\Event\Context\CollectionContext;
use Baleen\Migrations\Common\Event\Context\CollectionContextInterface;
use Baleen\Migrations\Common\Event\Progress;
use Baleen\Migrations\Version\VersionInterface;
use DateTime;

/**
 * Class MigrationEvent.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class MigrationEvent extends AbstractDomainEvent
{
    /** @var OptionsInterface */
    private $options;

    /** @var VersionInterface */
    private $target;

    /** @var CollectionContextInterface */
    private $context;

    /**
     * MigrationEvent constructor.
     *
     * @param VersionInterface $target
     * @param OptionsInterface $options
     * @param CollectionContextInterface $context
     * @param DateTime $occurredOn
     */
    public function __construct(
        VersionInterface $target,
        OptionsInterface $options,
        CollectionContextInterface $context = null,
        DateTime $occurredOn = null
    ) {
        if (null === $context) {
            $context = new CollectionContext(new Progress(1, 1));
        }
        $this->context = $context;

        $this->options = $options;
        $this->target = $target;

        parent::__construct($occurredOn);
    }

    /**
     * @return OptionsInterface
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Returns the Version that's being migrated.
     *
     * NOTE: Do not confuse this method with version()
     *
     * @return VersionInterface
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @return CollectionContextInterface
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @inheritdoc
     */
    protected function getAdditionalPayload()
    {
        return [
            'target' => $this->getTarget(),
            'options' => $this->getOptions(),
            'context' => $this->getContext(),
        ];
    }
}
