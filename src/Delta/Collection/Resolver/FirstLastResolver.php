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

namespace Baleen\Migrations\Delta\Collection\Resolver;

use Baleen\Migrations\Delta\Collection\Collection;
use Baleen\Migrations\Delta\DeltaInterface;

/**
 * Class FirstLastResolver
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
final class FirstLastResolver extends AbstractResolver
{
    const FIRST = 'first';
    const EARLIEST = 'earliest';
    const LAST = 'last';
    const LATEST = 'latest';

    /**
     * Resolves an alias into a Delta.
     *
     * @param string $alias
     *
     * @param Collection $collection
     * @return DeltaInterface|null
     */
    protected function doResolve($alias, Collection $collection)
    {
        $result = null;
        switch (strtolower($alias)) {
            case self::LAST:
            case self::LATEST:
                $result = $collection->last();
                break;
            case self::FIRST:
            case self::EARLIEST:
                $result = $collection->first();
                break;
            default:
        }
        return $result;
    }
}
