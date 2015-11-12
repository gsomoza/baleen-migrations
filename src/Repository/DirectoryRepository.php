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

namespace Baleen\Migrations\Repository;

use Baleen\Migrations\Exception\InvalidArgumentException;
use Baleen\Migrations\Migration\Factory\FactoryInterface;
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version\Collection\Linked;
use Baleen\Migrations\Version\Comparator\ComparatorInterface;
use Baleen\Migrations\Version\Comparator\MigrationComparator;
use Baleen\Migrations\Version\LinkedVersion;
use Zend\Code\Scanner\DerivedClassScanner;
use Zend\Code\Scanner\DirectoryScanner;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
final class DirectoryRepository extends AbstractRepository
{
    const PATTERN_DEFAULT = '/v([0-9]+).*/';

    /**
     * @var DirectoryScanner
     */
    private $scanner;

    /**
     * @var string
     */
    private $pattern = self::PATTERN_DEFAULT;

    /**
     * @param string $path Full path to the repository's directory
     * @param FactoryInterface $migrationFactory
     * @param ComparatorInterface $comparator
     * @param string $pattern Regex pattern to extract the version ID from a migration's class name. If null it will
     *                        default to DirectoryRepository::PATTERN_DEFAULT
     * @throws InvalidArgumentException
     */
    public function __construct(
        $path,
        FactoryInterface $migrationFactory = null,
        ComparatorInterface $comparator = null,
        $pattern = null
    ) {
        $path = (string) $path;
        if (empty($path) || !is_dir($path)) {
            throw new InvalidArgumentException('Argument "path" is empty or directory does not exist.');
        }

        $pattern = null === $pattern ? self::PATTERN_DEFAULT : (string) $pattern;
        if (empty($pattern)) {
            throw new InvalidArgumentException('Argument "pattern" cannot be empty.');
        }
        $this->pattern = $pattern;

        $this->scanner = new DirectoryScanner($path);

        parent::__construct($migrationFactory, $comparator);
    }

    /**
     * @inheritdoc
     */
    public function doFetchAll()
    {
        $versions = new Linked([], null, $this->getComparator());
        $classes = $this->scanner->getClasses(true);
        foreach ($classes as $class) {
            /* @var DerivedClassScanner $class */
            $className = $class->getName();
            $matches = [];
            if ($class->isInstantiable()
                && preg_match($this->pattern, $className, $matches)
                && isset($matches[1])
            ) {
                $migration = $this->getMigrationFactory()->create($className);
                if ($migration instanceof MigrationInterface) {
                    $id = hash('sha1', $className);
                    $version = new LinkedVersion($id, false, $migration);
                    $versions->add($version);
                }
            }
        }
        return $versions;
    }
}
