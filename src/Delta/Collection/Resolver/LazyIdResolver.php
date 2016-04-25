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

/**
 * Resolves version ID's. Should be lowest priority.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
final class LazyIdResolver extends AbstractResolver
{
    /**
     * @inheritdoc
     */
    public function doResolve($alias, Collection $collection)
    {
        // exit early if there's a full match
        if ($collection->contains($alias)) {
            return $collection->get($alias);
        }

        // lazy id search
        $length = strlen($alias);
        $candidate = null;
        foreach ($collection as $key => $version) {
            if (substr($key, 0, $length) === $alias) {
                if (null === $candidate) {
                    // make this version a candidate, but continue searching to see if any other items also meet the
                    // condition (in which case we'd know the $id being searched for is "ambiguous")
                    $candidate = $version;
                } else {
                    // the $id is ambiguous when used for lazy matching, therefore:
                    $candidate = null; // remove the candidate
                    break;
                }
            }
        }

        return $candidate;
    }
}
