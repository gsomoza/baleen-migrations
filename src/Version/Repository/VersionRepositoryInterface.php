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

namespace Baleen\Migrations\Version\Repository;

use Baleen\Migrations\Shared\Collection\CollectionInterface;
use Baleen\Migrations\Version\VersionId;
use Baleen\Migrations\Version\VersionInterface;

/**
 * Provides a collection of Versions that have been migrated.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
interface VersionRepositoryInterface
{
    /**
     * Fetch versions from the storage mapper.
     *
     * @return VersionId[]
     */
    public function fetchAll();

    /**
     * Fetch a single version from the storage mapper.
     *
     * @param string|VersionId $id
     *
     * @return VersionId
     */
    public function fetch($id);

    /**
     * Updates the storage by adding all versions from the collection that are migrated, and remove all versions which
     * are NOT migrated.
     *
     * @param CollectionInterface $versions
     *
     * @return bool Returns false on failure.
     */
    public function updateAll(CollectionInterface $versions);

    /**
     * Saves or deletes a version depending on whether the version is respectively migrated or not.
     *
     * @param VersionInterface $version
     * @return bool The result of calling 'save' or 'delete' on the version.
     */
    public function update(VersionInterface $version);

    /**
     * Adds a version into storage
     *
     * @param VersionInterface $version
     *
     * @return bool
     */
    public function save(VersionInterface $version);

    /**
     * Removes a version from storage
     *
     * @param VersionInterface $version
     *
     * @return bool
     */
    public function delete(VersionInterface $version);
}
