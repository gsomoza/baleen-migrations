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

namespace BaleenTest\Migrations\Delta\Comparator;

use Baleen\Migrations\Migration\MigrationInterface;
use BaleenTest\Migrations\BaseTestCase;
use BaleenTest\Migrations\Fixtures\Migrations\AllValid\v201507020419_InterfaceTest;
use BaleenTest\Migrations\Fixtures\Migrations\CustomRegex\v201507020437_DefaultRegex;

/**
 * Class ComparatorTestCase
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class ComparatorTestCase extends BaseTestCase
{
    /**
     * createMigration
     * @param $namespace
     * @param $name
     * @return MigrationInterface
     */
    protected function createMigration($namespace, $name)
    {
        $namespace = trim($namespace, "\\");
        $fqcn = "$namespace\\$name";
        if (!class_exists($fqcn)) {
            // looking forward to PHP 7 becoming the minimum supported version ;)
            eval(
            <<<CODE
namespace $namespace;
class $name implements \Baleen\Migrations\Migration\MigrationInterface {
    public function up() {}
    public function down() {}
}
CODE
            );
        }
        return new $fqcn();
    }

    /**
     * compare
     * @param $comparator
     * @return mixed
     */
    protected function simpleCompare($comparator)
    {
        // must have different namespaces
        $m1 = new v201507020419_InterfaceTest();
        $m2 = new v201507020437_DefaultRegex();

        $v1 = $this->buildVersion('abcd1', false, $m1);
        $v2 = $this->buildVersion('abcd2', false, $m2);

        return $comparator($v1, $v2);
    }
}
