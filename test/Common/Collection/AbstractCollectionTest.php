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

namespace BaleenTest\Migrations\Common\Collection;

use Baleen\Migrations\Common\Collection\AbstractCollection;
use Baleen\Migrations\Common\Collection\CollectionInterface;
use Baleen\Migrations\Delta\DeltaInterface;
use BaleenTest\Migrations\BaseTestCase;
use Mockery as m;

/**
 * Class AbstractCollectionTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class AbstractCollectionTest extends BaseTestCase
{
    /**
     * testConstructor
     */
    public function testConstructor()
    {
        $col = m::mock(AbstractCollection::class, [])
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
        $this->assertInstanceOf(CollectionInterface::class, $col);
    }

    /**
     * testRemove
     * @return void
     */
    public function _testRemove()
    {
        $col = $this->createCollection($this->buildVersions(['v1']));

        $v = $col->remove('v1');
        $this->assertInstanceOf(DeltaInterface::class, $v);
        $this->assertEquals('v1', $v->getId()->toString());
    }

    /**
     * createCollection
     * @return AbstractCollection|m\Mock
     */
    private function createCollection()
    {
        /** @var AbstractCollection|m\Mock $col */
        $col = m::mock(AbstractCollection::class, func_get_args());
        return $col;
    }
}
