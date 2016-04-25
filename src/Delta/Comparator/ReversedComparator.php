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

namespace Baleen\Migrations\Delta\Comparator;

use Baleen\Migrations\Delta\DeltaInterface;

/**
 * Class ReversedComparator
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
final class ReversedComparator extends AbstractComparator
{
    /** @var ComparatorInterface */
    private $internalComparator;

    /**
     * ReversedComparator constructor.
     *
     * @param ComparatorInterface $internalComparator The comparator to reverse.
     */
    public function __construct(ComparatorInterface $internalComparator)
    {
        $this->internalComparator = $internalComparator;
    }

    /**
     * Compares two versions with each other using the internal comparator, and returns the opposite result.
     *
     * @param DeltaInterface $version1
     * @param DeltaInterface $version2
     *
     * @return int
     */
    public function compare(DeltaInterface $version1, DeltaInterface $version2)
    {
        return $this->internalComparator->compare($version1, $version2) * -1;
    }

    /**
     * Returns a comparator that sorts in the opposite direction.
     *
     * @return static
     */
    public function getReverse()
    {
        return $this->internalComparator;
    }
}
