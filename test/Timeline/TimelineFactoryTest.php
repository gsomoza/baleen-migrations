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
use BaleenTest\Migrations\BaseTestCase;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class TimelineFactoryTest extends BaseTestCase
{

    public function testCreate()
    {
        $factory = new TimelineFactory(
            Version::fromArray('1', '2', '3', '4', '5'),
            Version::fromArray('1', '3', '4')
        );
        $timeline = $factory->create();
        $prop = new \ReflectionProperty($timeline, 'versions');
        $prop->setAccessible(true);
        $versions = $prop->getValue($timeline)->toArray();
        $expectedMigrated = [1 => true, 2 => false, 3 => true, 4 => true, 5 => false];
        $this->assertEquals($expectedMigrated, array_map(function (V $v) {
            return $v->isMigrated();
        }, $versions));
    }

    public function testCreateThrowsException()
    {
        $factory = new TimelineFactory(
            Version::fromArray('1', '2', '3', '4', '5'),
            Version::fromArray('1', '2', '3', '4', '5', '6') // has an additional version
        );

        $this->setExpectedException(MigrationMissingException::class);
        $timeline = $factory->create();
    }
}
