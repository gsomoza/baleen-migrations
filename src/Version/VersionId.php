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

namespace Baleen\Migrations\Version;

use Baleen\Migrations\Exception\InvalidArgumentException;
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Common\ValueObjectInterface;

/**
 * The simplest form of a Version
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
final class VersionId implements ValueObjectInterface
{
    const HASH_ALGORITHM = 'sha1';

    /** @var string */
    private $hash;

    /**
     * VersionId constructor.
     *
     * @param string $hash
     * @throws InvalidArgumentException
     */
    public function __construct($hash)
    {
        $hash = (string) $hash;
        if (empty($hash)) {
            throw new InvalidArgumentException('A version\'s hash cannot be empty.');
        }
        $this->hash = $hash;
    }

    /**
     * Returns a string representation of the value.
     *
     * @return string
     */
    public function toString()
    {
        return $this->hash;
    }

    /**
     * @inheritdoc
     */
    public function isSameValueAs(ValueObjectInterface $id)
    {
        if (!$id instanceof VersionId) {
            return false;
        }

        return $this->toString() === $id->toString();
    }

    /**
     * __toString
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * fromArray
     * @param $ids
     * @return VersionId[]
     */
    public static function fromArray(array $ids) {
        return array_map(function ($id) {
            return self::fromNative($id);
        }, $ids);
    }

    /**
     * @inheritdoc
     *
     * @throws InvalidArgumentException
     */
    public static function fromNative($value)
    {
        $str = (string) $value;
        if (empty($str)) {
            throw new InvalidArgumentException(
                'Refusing to create a VersionId from an empty string or any other type of value that casts into an ' .
                'empty string.'
            );
        }

        return new static(hash(self::HASH_ALGORITHM, $str));
    }

    /**
     * Creates a VersionId based on a migration class.
     *
     * @param MigrationInterface $migration
     *
     * @return VersionId
     *
     * @throws InvalidArgumentException
     */
    public static function fromMigration(MigrationInterface $migration)
    {
        $class = get_class($migration);
        return self::fromNative($class);
    }
}
