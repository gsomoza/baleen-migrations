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

namespace Baleen\Migrations\Common\Event;

use DateTime;

/**
 * Class AbstractDomainEvent
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
abstract class AbstractDomainEvent implements DomainEventInterface
{
    /** @var DateTime */
    private $occurredOn;

    /** @var int */
    private $version;

    /**
     * AbstractDomainEvent constructor.
     *
     * @param DateTime $occurredOn
     * @param int $version
     */
    public function __construct(DateTime $occurredOn = null, $version = null)
    {
        if (null === $occurredOn) {
            $occurredOn = new DateTime(); // now
        }
        $this->occurredOn = $occurredOn;
        $this->version = (int) $version;
    }

    /**
     * @inheritdoc
     */
    final public function getOccurredOn()
    {
        return $this->occurredOn;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @inheritdoc
     */
    final public function getPayload() {
        return array_merge([
            'occurred_on' => $this->getOccurredOn(),
            'version' => $this->getVersion(),
        ], $this->getAdditionalPayload());
    }

    /**
     * Custom getPayload fields, to be merged with default getPayload.
     *
     * @return array
     */
    abstract protected function getAdditionalPayload();
}
