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

namespace BaleenTest\Migrations\Fixtures\Migrations\CustomRegex;

use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Migration\OptionsInterface;

/**
 * Use the following regex to load this class with the DirectoryMigrationMapper: /Version_([0-9]+).*?/
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class Version_201507020433_CustomRegex implements MigrationInterface
{
    /**
     *
     */
    public function up()
    {
    }

    /**
     *
     */
    public function down()
    {
    }

    public function abort()
    {
        // TODO: Implement abort() method.
    }

    public function setRunOptions(OptionsInterface $options)
    {
        // TODO: Implement setOptions() method.
    }
}
