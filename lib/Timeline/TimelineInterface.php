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

use Baleen\Migration\MigrateOptions;
use Baleen\Version;

/**
 * The Timeline is responsible of emitting MigrateCommands based on how the user wants to navigate the timeline
 * (e.g. travel to a specific version). It takes into account the current state.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
interface TimelineInterface
{

    /**
     * Runs all versions up, starting from the oldest and until (and including) the specified version.
     *
     * @param string|\Baleen\Version $version
     * @param \Baleen\Migration\MigrateOptions $options
     * @return void
     */
    public function upTowards($version, MigrateOptions $options);

    /**
     * Runs all versions down, starting from the newest and until (and including) the specified version.
     *
     * @param string|\Baleen\Version $version
     * @param \Baleen\Migration\MigrateOptions $options
     * @return void
     */
    public function downTowards($version, MigrateOptions $options);

    /**
     * Runs migrations up/down so that all versions *before and including* the specified version are "up" and
     * all versions *after* the specified version are "down".
     *
     * @param $goalVersion
     * @param \Baleen\Migration\MigrateOptions $options
     * @return void
     */
    public function goTowards($goalVersion, MigrateOptions $options);

    /**
     * @param \Baleen\Version $version
     * @param \Baleen\Migration\MigrateOptions $options
     * @return
     */
    public function runSingle($version, MigrateOptions $options);

}
