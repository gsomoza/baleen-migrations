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

namespace Baleen\Migrations\Delta\Repository\Mapper;

use Baleen\Migrations\Exception\Version\Repository\Mapper\MapperException;
use Baleen\Migrations\Delta\DeltaId;

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
     * @return DeltaId[]
     */
    public function fetchAll();

    /**
     * Return whether the specified DeltaId exists in storage
     *
     * @param DeltaId $id
     *
     * @return DeltaId
     */
    public function fetch(DeltaId $id);

    /**
     * Saves an array of DeltaId objects
     *
     * @param DeltaId[] $ids
     *
     * @return bool True on success.
     *
     * @throws MapperException On failure.
     */
    public function saveAll(array $ids);

    /**
     * Saves an id to storage
     *
     * @param DeltaId $id
     *
     * @return bool True on success.
     *
     * @throws MapperException On failure.
     */
    public function save(DeltaId $id);

    /**
     * Deletes an array of DeltaId objects
     *
     * @param DeltaId[] $id
     *
     * @return bool True on success.
     */
    public function deleteAll(array $id);

    /**
     * Deletes an id from storage
     *
     * @param DeltaId $id
     *
     * @return bool True on success.
     *
     * @throws MapperException On failure.
     */
    public function delete(DeltaId $id);
}
