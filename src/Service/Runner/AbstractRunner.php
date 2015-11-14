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

namespace Baleen\Migrations\Service\Runner;

use Baleen\Migrations\Shared\Event\Context\ContextInterface;
use Baleen\Migrations\Shared\Event\MutePublisher;
use Baleen\Migrations\Shared\Event\PublisherInterface;

/**
 * Class AbstractRunner
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
abstract class AbstractRunner implements RunnerInterface
{
    /** @var PublisherInterface */
    private $publisher;

    /** @var ContextInterface */
    private $context;

    /**
     * AbstractRunner constructor.
     * @param PublisherInterface $publisher
     * @param ContextInterface $context
     */
    public function __construct(PublisherInterface $publisher = null, ContextInterface $context = null)
    {
        if (null === $publisher) {
            $publisher = new MutePublisher();
        }
        $this->publisher = $publisher;
        $this->context = $context;
    }

    /**
     * @return PublisherInterface
     */
    final protected function getPublisher()
    {
        return $this->publisher;
    }

    /**
     * getContext
     * @return ContextInterface|null
     */
    final protected function getContext() {
        return $this->context;
    }

    /**
     * @inheritdoc
     */
    final public function setContext(ContextInterface $context) {
        $this->context = $context;
    }

    /**
     * @inheritdoc
     */
    final public function clearContext()
    {
        $this->context = null;
    }
}
