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
use Baleen\Migrations\Migration\Factory\SimpleFactory;
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Version;
use Zend\Code\Scanner\DerivedClassScanner;
use Zend\Code\Scanner\DirectoryScanner;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class DirectoryRepository implements RepositoryInterface
{
    const PATTERN_DEFAULT = '/v([0-9]+).*/';

    /**
     * @var DirectoryScanner
     */
    private $scanner;

    /**
     * @var string
     */
    private $classNameRegex;

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @param $path
     * @param FactoryInterface $migrationFactory
     *
     * @throws InvalidArgumentException
     */
    public function __construct($path, FactoryInterface $migrationFactory = null)
    {
        if (empty($path) || !is_dir($path)) {
            throw new InvalidArgumentException('Argument "path" is empty or directory does not exist.');
        }
        $this->scanner = new DirectoryScanner($path);

        $this->classNameRegex = self::PATTERN_DEFAULT;

        if (null === $migrationFactory) {
            $migrationFactory = new SimpleFactory();
        }
        $this->factory = $migrationFactory;
    }

    /**
     * Returns all migrations available to the repository.
     *
     * @return array Array of Versions, each with a MigrationInstance object
     */
    public function fetchAll()
    {
        $versions = [];
        $classes = $this->scanner->getClasses(true);
        foreach ($classes as $class) {
            /* @var DerivedClassScanner $class */
            $className = $class->getName();
            $matches = [];
            if ($class->isInstantiable()
                && preg_match($this->classNameRegex, $className, $matches)
                && isset($matches[1])) {
                $migration = $this->factory->create($className);
                if ($migration instanceof MigrationInterface) {
                    /* @var \Baleen\Migrations\Migration\MigrationInterface $migration */
                    $version = new Version($matches[1]);
                    $version->setMigration($migration);
                    $versions[] = $version;
                }
            }
        }

        return $versions;
    }

    /**
     * @inheritdoc
     */
    public function setMigrationFactory(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @return string
     */
    public function getClassNameRegex()
    {
        return $this->classNameRegex;
    }

    /**
     * @param string $classNameRegex
     */
    public function setClassNameRegex($classNameRegex)
    {
        $this->classNameRegex = (string) $classNameRegex;
    }
}
