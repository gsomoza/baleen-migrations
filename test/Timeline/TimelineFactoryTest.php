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

use Baleen\Migrations\Exception\MigrationMissingException;
use Baleen\Migrations\Timeline\TimelineFactory;
use Baleen\Migrations\Version as V;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version\Collection\MigratedVersions;
use BaleenTest\Migrations\BaseTestCase;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class TimelineFactoryTest extends BaseTestCase
{

    public function testCreate()
    {
        $factory = new TimelineFactory(
            $this->createVersionsWithMigrations('1', '2', '3', '4', '5'),
            Version::fromArray('1', '2', '3', '4', '5')
        );
        $timeline = $factory->create();
        $prop = new \ReflectionProperty($timeline, 'versions');
        $prop->setAccessible(true);
        /** @var MigratedVersions $versions */
        $versions = $prop->getValue($timeline);
        foreach($versions as $v) {
            $this->assertTrue($v->isMigrated());
        }
    }

    public function testCreateThrowsException()
    {
        $factory = new TimelineFactory(
            $this->createVersionsWithMigrations('1', '2', '3', '4', '5'),
            Version::fromArray('1', '2', '3', '4', '5', '6') // has an additional version that doesn't have a migration
        );

        $this->setExpectedException(MigrationMissingException::class);
        $factory->create();
    }
}
