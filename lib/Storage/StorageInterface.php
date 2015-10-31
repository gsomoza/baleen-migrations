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

namespace Baleen\Migrations\Storage;

use Baleen\Migrations\Version;
use Baleen\Migrations\Version\Collection\MigratedVersions;
use Baleen\Migrations\Version\Comparator\ComparatorAwareInterface;

/**
 * Provides a collection of Versions that have been migrated.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
interface StorageInterface
{
    /**
     * Reads versions from the storage file.
     *
     * @return MigratedVersions
     */
    public function fetchAll();

    /**
     * Write a collection of versions to the storage file.
     *
     * @param MigratedVersions $versions
     *
     * @return bool Returns false on failure.
     */
    public function saveCollection(MigratedVersions $versions);

    /**
     * Saves or deletes a version depending on whether the version is respectively migrated or not.
     *
     * @param Version $version
     * @return bool The result of calling 'save' or 'delete' on the version.
     */
    public function update(Version $version);

    /**
     * Adds a version into storage
     * @param Version $version
     * @return bool
     */
    public function save(Version $version);

    /**
     * Removes a version from storage
     * @param Version $version
     * @return bool
     */
    public function delete(Version $version);
}
