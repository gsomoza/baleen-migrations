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

namespace Baleen\Migrations\Version\Collection\Resolver;

use Baleen\Migrations\Version\Collection\Collection;
use Baleen\Migrations\Version\VersionInterface;

/**
 * Class OffsetResolver.
 *
 * Resolves aliases in the format: ID{OPERATOR}[COUNT]
 *
 * Operators:
 *      +           will add
 *      -, ^ or ~   will subtract
 *
 * Repeat operators consecutively works as a shortcut for COUNT. E.g. ++ will set COUNT to 2.
 *
 * Count (optional) should be a number if present and takes precedence over the previous rule.
 *
 * Example aliases: 123+, 123++ (same as 123+2), 123+++9 (will be simplified to 123+9)
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
final class OffsetResolver extends AbstractResolver
{
    const PATTERN = '/^(.*?)([\+\-\~\^]+)([0-9]+)?$/';

    /**
     * @{inheritdoc}
     *
     * IMPROVE: this method has an NPath complexity of 400. The configured NPath complexity threshold is 200.
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @param string $alias
     * @param Collection $collection
     *
     * @return VersionInterface|null
     */
    protected function doResolve($alias, Collection $collection)
    {
        // parse alias
        $matches = [];
        if (!preg_match(self::PATTERN, $alias, $matches)) {
            return null;
        }
        list(, $newAlias, $operator) = $matches;

        // resolve the new alias (this will allow to resolve e.g. HEAD-1)
        $absoluteVersion = $collection->get($newAlias);
        if (null === $absoluteVersion) {
            return null;
        }

        // calculate the offset
        $count = !isset($matches[3]) ? strlen($operator) : (int) $matches[3];
        if (strlen($operator) > 1) {
            $operator = substr($operator, 0, 1);
        }
        $multiplier = $operator === '+' ? 1 : -1;
        $offset = $count * $multiplier;

        // find version by absolute getPosition + offset
        $absolutePos = $collection->getPosition($absoluteVersion);
        return $collection->getByPosition($absolutePos + $offset);
    }
}
