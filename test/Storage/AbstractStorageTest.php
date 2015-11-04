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
namespace BaleenTest\Migrations\Storage;

use Baleen\Migrations\Storage\AbstractStorage;
use Baleen\Migrations\Version\VersionInterface;
use BaleenTest\Migrations\BaseTestCase;
use Mockery as m;

/**
 * Class AbstractStorageTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 * TODO: testFetchAll (already covered but not specifically tested for use-cases)
 */
class AbstractStorageTest extends BaseTestCase
{
    /**
     * testUpdate
     * @param $isMigrated
     * @param $return
     *
     * @dataProvider updateProvider
     */
    public function testUpdate($isMigrated, $return)
    {
        /** @var m\Mock|VersionInterface $v */
        $v = m::mock(VersionInterface::class);
        $v->shouldReceive('isMigrated')->once()->andReturn($isMigrated);
        /** @var m\Mock|AbstractStorage $instance */
        $instance = m::mock(AbstractStorage::class)->makePartial();
        $instance->shouldReceive($isMigrated ? 'save' : 'delete')->once()->with($v)->andReturn($return);
        $result = $instance->update($v);
        $this->assertSame($return, $result);
    }

    /**
     * updateProvider
     * @return array
     */
    public function updateProvider()
    {
        $trueFalse = [true, false];
        $results = [m::mock(VersionInterface::class), null];
        return $this->combinations([$trueFalse, $results]);
    }
}
