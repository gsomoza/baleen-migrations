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

use Baleen\Migrations\Delta\Collection\Collection;
use BaleenTest\Migrations\BaseTestCase;
use Mockery as m;
use Zend\Stdlib\ArrayUtils;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
abstract class CollectionTestCase extends BaseTestCase
{
    /**
     * testConstructor
     * @return Collection
     */
    abstract public function testConstructor();

    /**
     * @depends testConstructor
     * @param Collection $instance
     * @return Collection
     */
    public function testAdd(Collection $instance)
    {
        $originalCount = count($instance);
        $version2 = $this->buildVersion(2, true);
        $instance->add($version2);
        $this->assertCount($originalCount + 1, $instance);

        return $instance;
    }

    /**
     * @depends testAdd
     * @param Collection $collection
     */
    public function testRemove(Collection $collection)
    {
        $originalCount = $collection->count();

        // test remove by version
        $version = $collection->first();
        $collection->remove($version); // here we're also testing remove casts parameter to string
        $this->assertCount($originalCount - 1, $collection);

        // test remove by id
        $version = $collection->first();
        $collection->remove($version->getId());
        $this->assertCount($originalCount - 2, $collection);
    }
}
