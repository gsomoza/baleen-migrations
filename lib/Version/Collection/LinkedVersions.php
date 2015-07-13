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

namespace Baleen\Migrations\Version\Collection;

use Baleen\Migrations\Exception\MigrationMissingException;
use Baleen\Migrations\Version;

/**
 * Represents a set of Versions, all of which must be linked to a Migration
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class LinkedVersions extends SortableVersions
{
    /**
     * Validates that migrations added to this set must all have a linked Migration
     *
     * @param mixed $version
     * @return bool
     *
     * @throws MigrationMissingException
     */
    public function isAcceptable($version)
    {
        parent::isAcceptable($version);
        /** @var Version $version */
        if (!$version->hasMigration()) {
            throw new MigrationMissingException(sprintf(
                'Version "%s" must be associated with a Migration in order to be accepted into this collection.',
                $version->getId()
            ));
        }
        return true;
    }
}
