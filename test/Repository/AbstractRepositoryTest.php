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
namespace BaleenTest\Migrations\Repository;


use Baleen\Migrations\Exception\RepositoryException;
use Baleen\Migrations\Migration\Factory\FactoryInterface;
use Baleen\Migrations\Repository\AbstractRepository;
use BaleenTest\Migrations\BaseTestCase;
use Mockery as m;

/**
 * Class AbstractRepositoryTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class AbstractRepositoryTest extends BaseTestCase
{

    /**
     * testProvidesDefaultFactory
     */
    public function testProvidesDefaultFactory()
    {
        // pass constructor arguments to the partial in order to trigger the constructor
        $instance = m::mock(AbstractRepository::class, [null])->makePartial();
        $method = new \ReflectionMethod($instance, 'getMigrationFactory');
        $method->setAccessible(true);
        $this->assertInstanceOf(FactoryInterface::class, $method->invoke($instance));
    }

    /**
     * @param $return
     * @dataProvider doFetchResultIsNotLinkedCollectionProvider
     */
    public function testDoFetchResultIsNotLinkedCollection($return)
    {
        /** @var AbstractRepository|m\Mock $instance */
        $instance = m::mock(AbstractRepository::class)->shouldAllowMockingProtectedMethods()->makePartial();
        $instance->shouldReceive('doFetchAll')->once()->andReturn($return);
        $this->setExpectedException(RepositoryException::class, 'Linked');
        $instance->fetchAll();
    }

    /**
     * doFetchResultIsNotLinkedCollectionProvider
     * @return array
     */
    public function doFetchResultIsNotLinkedCollectionProvider()
    {
        return [
            ['scalar'],
            [new \stdClass()],
        ];
    }
}
