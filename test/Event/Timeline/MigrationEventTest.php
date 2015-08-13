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

namespace BaleenTest\Migrations\Event\Timeline;

use Baleen\Migrations\Event\Timeline\MigrationEvent;
use Baleen\Migrations\Event\Timeline\Progress;
use Baleen\Migrations\Migration\Options;
use Baleen\Migrations\Version as V;
use BaleenTest\Migrations\BaseTestCase;
use Mockery as m;

/**
 * Class MigrationEventTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class MigrationEventTest extends BaseTestCase
{
    /**
     * testConstructorCreatesProgressIfNull
     */
    public function testConstructorCreatesProgressIfNull()
    {
        $instance = new MigrationEvent(new V(1), new Options(Options::DIRECTION_UP));
        $progress = $instance->getProgress();
        $this->assertInstanceOf(Progress::class, $progress);
        $this->assertEquals(1, $progress->getTotal());
        $this->assertEquals(1, $progress->getCurrent());
    }

    /**
     * testConstructorSetsProgress
     */
    public function testConstructorSetsProgress()
    {
        $instance = new MigrationEvent(new V(1), new Options(Options::DIRECTION_UP), new Progress(10, 5));
        $progress = $instance->getProgress();
        $this->assertInstanceOf(Progress::class, $progress);
        $this->assertEquals(10, $progress->getTotal());
        $this->assertEquals(5, $progress->getCurrent());
    }
}
