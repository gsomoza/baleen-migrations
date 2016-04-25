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

namespace Baleen\Migrations\Migration\Repository\Mapper;

use Baleen\Migrations\Exception\InvalidArgumentException;
use Baleen\Migrations\Migration\Factory\FactoryInterface;
use Baleen\Migrations\Migration\Factory\SimpleFactory;
use Baleen\Migrations\Migration\MigrationInterface;
use Zend\Code\Scanner\DerivedClassScanner;
use Zend\Code\Scanner\DirectoryScanner;

/**
 * Class DirectoryRepositoryMapper
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
final class DirectoryMapper implements MigrationMapperInterface
{
    const PATTERN_DEFAULT = '/v([0-9]+).*/';

    /**
     * @var DirectoryScanner
     */
    private $scanner;

    /** @var string */
    private $pattern = self::PATTERN_DEFAULT;

    /** @var FactoryInterface */
    private $factory;

    /**
     * @param string $path Full path to the repository's directory
     * @param FactoryInterface $factory A factory that can instantiate migrations.
     * @param string $pattern Regex pattern to extract the version ID from a migration's class name. If null it will
     *                        default to DirectoryRepositoryMapper::PATTERN_DEFAULT
     * @throws InvalidArgumentException
     */
    public function __construct(
        $path,
        FactoryInterface $factory = null,
        $pattern = null
    ) {
        $path = (string) $path;
        if (empty($path) || !is_dir($path)) {
            throw new InvalidArgumentException('Argument "path" is empty or directory does not exist.');
        }
        $this->scanner = new DirectoryScanner($path);

        $pattern = null === $pattern ? self::PATTERN_DEFAULT : (string) $pattern;
        if (empty($pattern)) {
            throw new InvalidArgumentException('Argument "pattern" cannot be empty.');
        }
        $this->pattern = $pattern;

        if (null === $factory) {
            $factory = new SimpleFactory();
        }
        $this->factory = $factory;
    }

    /**
     * @inheritdoc
     */
    public function fetchAll()
    {
        $classes = $this->scanner->getClasses(true);
        $definitions = [];
        foreach ($classes as $class) {
            /* @var DerivedClassScanner $class */
            $className = $class->getName();
            $matches = [];
            if (preg_match($this->pattern, $className, $matches)
                && isset($matches[1])
                && $class->isInstantiable()
            ) {
                $migration = $this->factory->create($className);
                if ($migration instanceof MigrationInterface) {
                    $definitions[] = new Definition($migration);
                }
            }
        }

        return $definitions;
    }
}
