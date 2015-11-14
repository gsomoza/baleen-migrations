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
namespace Baleen\Migrations\Version\Comparator;

use Baleen\Migrations\Migration\Options\Direction;
use Baleen\Migrations\Version\VersionInterface;

/**
 * Class AbstractComparator
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
abstract class AbstractComparator implements ComparatorInterface
{
    /** @var int */
    private $order = 1;

    /**
     * MigrationComparator constructor.
     *
     * @param Direction $direction
     */
    public function __construct(Direction $direction = null)
    {
        if (null === $direction) {
            $direction = Direction::up();
        }
        $this->order = $direction->isUp() ? 1 : -1;
    }

    /**
     * @inheritDoc
     */
    final public function __invoke(VersionInterface $version1, VersionInterface $version2)
    {
        return $this->compare($version1, $version2);
    }

    /**
     * @inheritdoc
     */
    public function withDirection(Direction $direction)
    {
        return new static($direction);
    }

    /**
     * @inheritdoc
     */
    final public function compare(VersionInterface $version1, VersionInterface $version2)
    {
        return $this->doCompare($version1, $version2) * $this->order;
    }

    /**
     * The internal compare function. Should return less than zero (0), zero or greater than zero if the first item is
     * respectively less than, equal to, or greater than the second item.
     *
     * @param VersionInterface $version1
     * @param VersionInterface $version2
     *
     * @return mixed
     */
    abstract protected function doCompare(VersionInterface $version1, VersionInterface $version2);
}
