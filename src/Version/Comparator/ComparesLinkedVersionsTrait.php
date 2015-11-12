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

use Baleen\Migrations\Exception\InvalidArgumentException;
use Baleen\Migrations\Version\LinkedVersionInterface;
use Baleen\Migrations\Version\VersionInterface;

/**
 * Class ComparesLinkedVersionsTrait
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
trait ComparesLinkedVersionsTrait
{
    /**
     * Validates the version is a LinkedVersion and returns the class name of its migration
     *
     * @param VersionInterface $version
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    final protected function getMigrationClass(VersionInterface $version)
    {
        if (!$version instanceof LinkedVersionInterface) {
            throw new InvalidArgumentException(sprintf(
                "Expected version %s to be linked to a migration",
                $version->getId()
            ));
        }
        return get_class($version->getMigration());
    }
}
