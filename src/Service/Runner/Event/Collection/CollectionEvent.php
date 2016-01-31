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

namespace Baleen\Migrations\Service\Runner\Event\Collection;

use Baleen\Migrations\Migration\OptionsInterface;
use Baleen\Migrations\Common\Collection\CollectionInterface;
use Baleen\Migrations\Common\Event\AbstractDomainEvent;
use Baleen\Migrations\Version\VersionInterface;
use DateTime;

/**
 * Class CollectionEvent.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class CollectionEvent extends AbstractDomainEvent
{
    /**
     * @var CollectionInterface
     */
    private $collection;

    /**
     * @var OptionsInterface
     */
    private $options;

    /**
     * @var VersionInterface
     */
    private $target;

    /**
     * CollectionEvent constructor.
     *
     * @param VersionInterface $target
     * @param OptionsInterface $options
     * @param CollectionInterface $collection
     * @param DateTime $createdOn
     */
    public function __construct(
        VersionInterface $target,
        OptionsInterface $options,
        CollectionInterface $collection,
        DateTime $createdOn = null
    ) {
        $this->options = $options;
        $this->target = $target;
        $this->collection = $collection;
        parent::__construct($createdOn);
    }

    /**
     * @return OptionsInterface
     */
    final public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return CollectionInterface
     */
    final public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @return VersionInterface
     */
    final public function getTarget()
    {
        return $this->target;
    }

    /**
     * Returns the event getPayload
     *
     * @return array
     */
    protected function getAdditionalPayload()
    {
        return [
            'target' => $this->getTarget(),
            'options' => $this->getOptions(),
            'collection' => $this->getCollection(),
        ];
    }
}
