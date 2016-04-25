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

namespace Baleen\Migrations\Delta\Comparator;

use Baleen\Migrations\Migration\Options\Direction;
use Baleen\Migrations\Delta\DeltaInterface;

/**
 * Compares two version with each other.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
interface ComparatorInterface
{
    /**
     * MUST call static::compare()
     *
     * @param DeltaInterface $version1
     * @param DeltaInterface $version2
     *
     * @return int
     */
    public function __invoke(DeltaInterface $version1, DeltaInterface $version2);

    /**
     * Compares two versions with each other. The comparison function must return an integer less than, equal to, or
     * greater than zero if the first argument is considered to be respectively less than, equal to, or greater than the
     * second.
     *
     * @param DeltaInterface $version1
     * @param DeltaInterface $version2
     *
     * @return int
     */
    public function compare(DeltaInterface $version1, DeltaInterface $version2);

    /**
     * Returns a new comparator that sorts in the opposite direction.
     *
     * @return static
     */
    public function getReverse();
}
