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

namespace BaleenTest\Migrations\Version\Collection\Resolver;

use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Version\Collection\Collection;
use Baleen\Migrations\Version\Collection\Resolver\FilenameResolver;
use BaleenTest\Migrations\BaseTestCase;
use BaleenTest\Migrations\Fixtures\Migrations\AllValid\v201507020419_InterfaceTest;
use Mockery as m;

/**
 * Class FilenameResolverTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class FilenameResolverTest extends BaseTestCase
{
    /**
     * testResolve
     * @param MigrationInterface $migration
     * @param $filename
     * @param bool $expectNull
     * @throws \Baleen\Migrations\Exception\Version\Collection\ResolverException
     * @dataProvider resolveProvider
     */
    public function testResolve(MigrationInterface $migration, $filename, $expectNull = false)
    {
        $v1 = $this->buildVersion(1);
        $v2 = $this->buildVersion(2, false, $migration);
        $collection = new Collection([$v1, $v2]);
        $resolver = new FilenameResolver();
        $result = $resolver->resolve($filename, $collection);
        if ($expectNull) {
            $this->assertNull($result);
        } else {
            $this->assertSame($v2, $result);
        }
    }

    /**
     * resolveProvider
     */
    public function resolveProvider()
    {
        $migration = new v201507020419_InterfaceTest();
        return [
            // good matches
            [$migration, 'v201507020419_InterfaceTest.php'],
            [$migration, 'Test.php'],
            [$migration, 'AllValid' . DIRECTORY_SEPARATOR . 'v201507020419_InterfaceTest.php'],
            // bad matches
            [$migration, 'AllValid' . DIRECTORY_SEPARATOR . 'Test.php', true], // no funkiness
            [$migration, '*Test.php', true], // no wildcards
            [$migration, 'v201507020419_InterfaceTest', true], // no file extension
            [$migration, 'v201507020419.php', true], // partial filename match and ends in .php
        ];
    }
}
