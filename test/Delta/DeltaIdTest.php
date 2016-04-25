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

namespace BaleenTest\Migrations\Delta;

use Baleen\Migrations\Exception\InvalidArgumentException;
use Baleen\Migrations\Migration\Options;
use Baleen\Migrations\Delta\DeltaId;
use BaleenTest\Migrations\BaseTestCase;
use BaleenTest\Migrations\Fixtures\Migrations\AllValid\v201507020418_SimpleTest;
use Mockery as m;

/**
 * Class DeltaIdTest
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class DeltaIdTest extends BaseTestCase
{
    /**
     * testSameValueAs
     * @return void
     */
    public function testSameValueAs()
    {
        $id1 = new DeltaId('v2');
        $id2 = new DeltaId('v2');
        $id3 = new DeltaId('v10');
        $this->assertTrue($id1->isSameValueAs($id2));
        $this->assertFalse($id2->isSameValueAs($id3));
    }

    /**
     * testSameValueAsDifferentValueObjects
     * @return void
     */
    public function testSameValueAsDifferentValueObjects()
    {
        $id = new DeltaId('v1');
        $opt = new Options();
        $this->assertFalse($id->isSameValueAs($opt));
    }

    /**
     * testFromNative
     * @param $val
     * @param $whatToHash
     * @param bool $exception
     * @throws InvalidArgumentException
     * @dataProvider fromNativeProvider
     */
    public function testFromNative($val, $whatToHash, $exception = false)
    {
        if ($exception) {
            $this->setExpectedException(InvalidArgumentException::class);
        }
        $expected = $this->getHash($whatToHash);
        $res = DeltaId::fromNative($val);
        $this->assertEquals($expected, $res->toString());
    }

    /**
     * fromNativeProvider
     * @return array
     */
    public function fromNativeProvider()
    {
        return [
            [null, '', true],
            [1, '1'],
            ['2', '2'],
            [3.1, '3.1'],
            [true, '1'],
            [false, '0', true],
        ];
    }

    /**
     * testFromMigration
     * @return void
     */
    public function testFromMigration()
    {
        $hash = $this->getHash(v201507020418_SimpleTest::class);
        $v = DeltaId::fromMigration(new v201507020418_SimpleTest());
        $this->assertEquals($hash, $v->toString());
    }

    /**
     * getHash
     * @param $whatToHash
     * @return string
     */
    private function getHash($whatToHash) {
        return hash(DeltaId::HASH_ALGORITHM, $whatToHash);
    }

    /**
     * testFromArray
     * @param array $array
     * @param $expected
     * @param bool $exception
     * @dataProvider fromArrayProvider
     */
    public function testFromArray(array $array, $expected, $exception = false)
    {
        if ($exception) {
            $this->setExpectedException(InvalidArgumentException::class);
        }
        $v = DeltaId::fromArray($array);
        $this->assertCount(count($array), $v);

        $ids = array_map('strval', $v);
        foreach ($expected as $item) {
            $this->assertContains($this->getHash($item), $ids);
        }
    }

    /**
     * fromArrayProvider
     * @return array
     */
    public function fromArrayProvider()
    {
        return [
            [[], []],
            [[1, '2', 3.1, true], ['1', '2', '3.1', '1']],
            // errors
            [[null], [], true],
            [[''], [], true],
            [[false], [], true],
        ];
    }
}
