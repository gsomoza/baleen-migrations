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

namespace Baleen\Migrations\Version\Repository\Mapper;

use Baleen\Migrations\Exception\Version\Repository\Mapper\MapperException;
use Baleen\Migrations\Version\VersionId;

/**
 * Interface VersionMapperInterface
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
interface VersionMapperInterface
{
    /**
     * Fetches all available version ids
     *
     * @return VersionId[]
     */
    public function fetchAll();

    /**
     * Return whether the specified VersionId exists in storage
     *
     * @param VersionId $id
     *
     * @return VersionId
     */
    public function fetch(VersionId $id);

    /**
     * Saves an array of VersionId objects
     *
     * @param VersionId[] $ids
     *
     * @return bool True on success.
     *
     * @throws MapperException On failure.
     */
    public function saveAll(array $ids);

    /**
     * Saves an id to storage
     *
     * @param VersionId $id
     *
     * @return bool True on success.
     *
     * @throws MapperException On failure.
     */
    public function save(VersionId $id);

    /**
     * Deletes an id from storage
     *
     * @param VersionId $id
     *
     * @return bool True on success.
     *
     * @throws MapperException On failure.
     */
    public function delete(VersionId $id);
}
