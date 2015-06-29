<?php

namespace Baleen\Repository;

use Baleen\Exception\InvalidArgumentException;
use Baleen\Version;
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
     * @param $path
     * @param string $classNameRegex Regexp used to extract ID from a Migration class name. The first match must be the ID.
     * @throws InvalidArgumentException
     */
    public function __construct($path, $classNameRegex = self::PATTERN_DEFAULT) {
        if (empty($path) || !is_dir($path)) {
            throw new InvalidArgumentException('Argument "path" is empty or directory does not exist.');
        }
        $this->scanner = new DirectoryScanner($path);

        $this->classNameRegex = $classNameRegex;
    }

    /**
     * Returns all migrations available to the repository
     * @return array Array of Versions, each with a MigrationInstance object
     */
    public function fetchAll()
    {
        $versions = [];
        $classes = $this->scanner->getClasses(true);
        foreach ($classes as $class) {
            /** @var DerivedClassScanner $class */
            $className = $class->getName();
            if ($class->isInstantiable()
                && in_array('Baleen\Migration\MigrationInterface', $class->getInterfaces()) )
            {
                $matches = [];
                if (preg_match($this->classNameRegex, $className, $matches) && isset($matches[1])) {
                    /** @var \Baleen\Migration\MigrationInterface $migration */
                    $migration = new $className();
                    $version = new Version($matches[1]);
                    $version->setMigration($migration);
                    $versions[] = $version;
                }
            }
        }
        return $versions;
    }
}
