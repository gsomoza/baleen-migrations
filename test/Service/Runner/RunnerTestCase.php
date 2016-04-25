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

namespace BaleenTest\Migrations\Service\Runner;

use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Delta\DeltaInterface as V;
use BaleenTest\Migrations\BaseTestCase;
use Mockery as m;

/**
 * Class RunnerTestCase
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class RunnerTestCase extends BaseTestCase
{

    /**
     * getAllMigratedVersionsFixture
     * NOTE: testRunCollection expects this to return exactly 12 fixtures
     * @return V[]
     */
    public function getAllMigratedVersionsFixture()
    {
        return $this->getFixtureFor([
            ['id' => 'v01', 'migrated' => true],
            ['id' => 'v02', 'migrated' => true],
            ['id' => 'v03', 'migrated' => true],
            ['id' => 'v04', 'migrated' => true],
            ['id' => 'v05', 'migrated' => true],
            ['id' => 'v06', 'migrated' => true],
            ['id' => 'v07', 'migrated' => true],
            ['id' => 'v08', 'migrated' => true],
            ['id' => 'v09', 'migrated' => true],
            ['id' => 'v10', 'migrated' => true],
            ['id' => 'v11', 'migrated' => true],
            ['id' => 'v12', 'migrated' => true],
        ]);
    }

    /**
     * getNoMigratedVersionsFixture
     * NOTE: testRunCollection expects this to return exactly 12 fixtures
     * @return V[]
     */
    public function getNoMigratedVersionsFixture()
    {
        return $this->getFixtureFor([
            ['id' => 'v01', 'migrated' => false],
            ['id' => 'v02', 'migrated' => false],
            ['id' => 'v03', 'migrated' => false],
            ['id' => 'v04', 'migrated' => false],
            ['id' => 'v05', 'migrated' => false],
            ['id' => 'v06', 'migrated' => false],
            ['id' => 'v07', 'migrated' => false],
            ['id' => 'v08', 'migrated' => false],
            ['id' => 'v09', 'migrated' => false],
            ['id' => 'v10', 'migrated' => false],
            ['id' => 'v11', 'migrated' => false],
            ['id' => 'v12', 'migrated' => false],
        ]);
    }

    /**
     * getMixedVersionsFixture
     * NOTE: testRunCollection expects this to return exactly 12 fixtures
     *
     * @return V[]
     */
    public function getMixedVersionsFixture()
    {
        return $this->getFixtureFor([
            ['id' => 'v01', 'migrated' => true],
            ['id' => 'v02', 'migrated' => false],
            ['id' => 'v03', 'migrated' => true],
            ['id' => 'v04', 'migrated' => true],
            ['id' => 'v05', 'migrated' => false],
            ['id' => 'v06', 'migrated' => false],
            ['id' => 'v07', 'migrated' => false],
            ['id' => 'v08', 'migrated' => true],
            ['id' => 'v09', 'migrated' => false],
            ['id' => 'v10', 'migrated' => true],
            ['id' => 'v11', 'migrated' => false],
            ['id' => 'v12', 'migrated' => false],
        ]);
    }

    /**
     * returns a matrix of migrated statuses for the mixed versions fixture
     *
     * NOTE: testRunCollection expects this to return exactly 12 items
     *
     * @return array
     */
    private function getMixedMatrix()
    {
        return [1, 0, 1, 1, 0, 0, 0, 1, 0, 1, 0, 0];
    }

    /**
     * Updates the mixed matrix
     * @param array $indexes Which indexes will be updated
     * @param int $migrated Which status will they get (1 for migrated, 0 otherwise)
     * @return array
     */
    protected function getUpdatedMixedMatrix(array $indexes, $migrated)
    {
        $matrix = $this->getMixedMatrix();
        foreach ($indexes as $i) {
            $matrix[$i] = $migrated;
        }
        return $matrix;
    }

    /**
     * This fixture is meant to cover all use-cases.
     *
     * @param array $versions
     * @return V[]
     */
    public function getFixtureFor(array $versions)
    {
        /** @var MigrationInterface|m\Mock $migrationMock */
        $migrationMock = m::mock(MigrationInterface::class);
        $migrationMock->shouldReceive('up')->zeroOrMoreTimes();
        $migrationMock->shouldReceive('down')->zeroOrMoreTimes();
        $migrationMock->shouldReceive('abort')->zeroOrMoreTimes();
        $migrationMock->shouldReceive('setOptions')->zeroOrMoreTimes();
        $self = $this;
        return array_map(function ($arr) use ($migrationMock, $self) {
            return $self->buildVersion($arr['id'], $arr['migrated'], clone $migrationMock);
        }, $versions);
    }
}
