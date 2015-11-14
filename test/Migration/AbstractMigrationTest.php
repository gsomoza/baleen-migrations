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
 * <https://github.com/baleen/migrations>.
 */

namespace BaleenTest\Migrations\Migration\Factory;

use Baleen\Migrations\Migration\AbstractMigration;
use Baleen\Migrations\Migration\Options;
use BaleenTest\Migrations\BaseTestCase;
use Mockery as m;

/**
 * Class AbstractMigrationTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class AbstractMigrationTest extends BaseTestCase
{

    public function testGetSetOptions()
    {
        $options = new Options();
        /** @var AbstractMigration|m\Mock $instance */
        $instance = m::mock(AbstractMigration::class)->makePartial();
        $instance->setOptions($options);
        $this->assertSame($options, $this->invokeMethod('getOptions', $instance));
    }

}
