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

namespace Baleen\Timeline;

use Baleen\Timeline;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class TimelineFactory
{

    /** @var array */
    private $availableVersions;

    /** @var array */
    private $migratedVersions;

    /**
     * @param array $availableVersions
     * @param array $migratedVersions
     */
    public function __construct(array $availableVersions, array $migratedVersions)
    {
        $this->availableVersions = $availableVersions;
        $this->migratedVersions = $migratedVersions;
    }

    /**
     * Creates a Timeline instance with all available versions. Those versions that have already been migrated will
     * be marked accordingly.
     *
     * @return Timeline
     */
    public function create()
    {
        foreach ($this->migratedVersions as $version) {
            /** @var \Baleen\Version $version */
            if (!empty($this->availableVersions[$version->getId()])) {
                /** @var \Baleen\Version $availableVersion */
                $availableVersion = $this->availableVersions[$version->getId()];
                $availableVersion->setMigrated(true);
            } //TODO: else throw an exception
        }
        return new Timeline($this->availableVersions);
    }

}
