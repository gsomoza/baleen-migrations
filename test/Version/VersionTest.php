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

namespace BaleenTest\Migrations\Version;

use Baleen\Migrations\Exception\InvalidArgumentException;
use Baleen\Migrations\Service\MigrationBus\MigrateCommand;
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Migration\OptionsInterface;
use Baleen\Migrations\Common\EntityInterface;
use Baleen\Migrations\Version\Version;
use Baleen\Migrations\Version\VersionId;
use Baleen\Migrations\Version\VersionInterface;
use BaleenTest\Migrations\BaseTestCase;
use Mockery as m;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class VersionTest extends BaseTestCase
{
    /**
     * testIdCannotBeEmpty
     */
    public function testIdCannotBeEmpty()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->buildVersion('');
    }

    /**
     * testToString
     */
    public function testToString()
    {
        $id = 'v1';
        $v = $this->buildVersion($id);
        $this->assertEquals($id, (string) $v);
    }

    /**
     * testConstructorWithoutId
     * @return void
     */
    public function testConstructorWithoutId()
    {
        /** @var MigrationInterface|m\Mock $m */
        $m = m::mock(MigrationInterface::class);
        $hash = hash(VersionId::HASH_ALGORITHM, get_class($m));
        $v = new Version($m, false);
        $this->assertEquals($hash, $v->getId()->toString());
    }

    /**
     * testSameIdentityAs
     * @return void
     */
    public function testSameIdentityAs()
    {
        /** @var VersionInterface $v1 */
        /** @var VersionInterface $v2 */
        list($v1, $v2) = $this->buildVersions([1,2]);
        $v1bis = $this->buildVersion(1);
        /** @var EntityInterface|m\Mock $anotherEntity */
        $anotherEntity = m::mock(EntityInterface::class);

        $this->assertTrue($v1->isSameIdentityAs($v1bis));
        $this->assertFalse($v1bis->isSameIdentityAs($v2));
        $this->assertFalse($v2->isSameIdentityAs($v1));
        $this->assertFalse($v1->isSameIdentityAs($anotherEntity));
    }
}
