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

namespace Baleen\Migrations\Service\MigrationBus;

use Baleen\Migrations\Exception\MigrationBusException;
use Baleen\Migrations\Service\MigrationBus\Middleware\SetOptionsMiddleware;
use Baleen\Migrations\Service\MigrationBus\Middleware\TransactionMiddleware;
use League\Tactician\CommandBus;

final class MigrationBus extends CommandBus implements MigrationBusInterface
{
    /**
     * @inheritdoc
     */
    public function __construct(array $middleware)
    {
        $foundHandler = false;
        foreach ($middleware as $object) {
            if ($object instanceof MigrateHandler) {
                $foundHandler = true;
                break;
            }
        }
        if (!$foundHandler) {
            throw new MigrationBusException(sprintf(
                'MigrationBus must have at least one instance of "%s"',
                MigrateHandler::class
            ));
        }
        parent::__construct($middleware);
    }

    /**
     * factory
     * @return MigrationBus
     */
    public static function createDefaultBus()
    {
        return new MigrationBus(static::getDefaultMiddleWare());
    }

    /**
     * getDefaultMiddleWare
     * @return array
     */
    public static function getDefaultMiddleWare()
    {
        return [
            new SetOptionsMiddleware(),
            new TransactionMiddleware(),
            new MigrateHandler(),
        ];
    }
}
