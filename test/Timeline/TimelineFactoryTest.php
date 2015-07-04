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

use Baleen\Version as V;
use BaleenTest\BaseTestCase;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class TimelineFactoryTest extends BaseTestCase
{

    public function testCreate()
    {
        $factory = new \Baleen\Timeline\TimelineFactory(
            [1 => new V(1), 2 => new V(2), 3 => new V(3), 4 => new V(4), 5 => new V(5)],
            [1 => new V(1), 3 => new V(3), 4 => new V(4)]
        );
        $timeline = $factory->create();
        $prop = new \ReflectionProperty($timeline, 'versions');
        $prop->setAccessible(true);
        $versions = $prop->getValue($timeline);
        $expectedMigrated = [1 => 1, 2 => 0, 3 => 1, 4 => 1, 0];
        $this->assertEquals($expectedMigrated, array_map(function (V $v) {
            return $v->isMigrated() ? 1 : 0;
        }, $versions));
    }
}
