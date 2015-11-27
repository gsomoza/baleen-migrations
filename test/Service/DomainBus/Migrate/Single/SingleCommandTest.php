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

namespace BaleenTest\Migrations\Service\DomainBus\Migrate\Single;

use Baleen\Migrations\Migration\OptionsInterface;
use Baleen\Migrations\Service\DomainBus\Migrate\Single\SingleCommand;
use Baleen\Migrations\Shared\Event\Context\CollectionContextInterface;
use Baleen\Migrations\Version\Repository\VersionRepositoryInterface;
use Baleen\Migrations\Version\VersionInterface;
use BaleenTest\Migrations\BaseTestCase;
use BaleenTest\Migrations\Version\Repository\VersionRepositoryTest;
use Mockery as m;

/**
 * Class SingleCommandTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class SingleCommandTest extends BaseTestCase
{
    /**
     * createMockedCommand
     * @return SingleCommand
     */
    public static function createMockedCommand()
    {
        /** @var VersionInterface|m\Mock $target */
        $target = m::mock(VersionInterface::class);
        /** @var OptionsInterface $options */
        $options = m::mock(OptionsInterface::class);
        /** @var VersionRepositoryInterface|m\Mock $storage */
        $storage = m::mock(VersionRepositoryInterface::class);
        return new SingleCommand($target, $options, $storage);
    }

    /**
     * testConstructorWithoutContext
     * @return void
     */
    public function testConstructorWithoutContext()
    {
        $command = self::createMockedCommand();
        $context = $command->getContext();
        $this->assertInstanceOf(CollectionContextInterface::class, $context);
        $this->assertEquals(1, $command->getContext()->getProgress()->getTotal());
        $this->assertEquals(1, $command->getContext()->getProgress()->getCurrent());
    }
}
