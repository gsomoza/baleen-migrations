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

namespace Baleen\Migrations\Version\Validator;

use Baleen\Migrations\Exception\Version\ValidationException;
use Baleen\Migrations\Version\SpecificationInterface;
use Baleen\Migrations\Version\VersionInterface;

/**
 * Class LinkedValidator
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
final class AggregateValidator implements ValidatorInterface
{
    /** @var SpecificationInterface[] */
    private $specs = [];

    /**
     * AggregateValidator constructor.
     *
     * @param SpecificationInterface[] $specs
     *
     * @throws ValidationException
     */
    public function __construct(array $specs)
    {
        foreach ($specs as $spec) {
            if (!is_object($spec) || !$spec instanceof SpecificationInterface) {
                throw new ValidationException(sprintf(
                    'Expected an array of %s objects, but one of the items is a %s.',
                    SpecificationInterface::class,
                    is_object($spec) ? get_class($spec) : gettype($spec)
                ));
            }
            // this also ensures they're unique
            $this->specs[get_class($spec)] = $spec;
        }
    }

    /**
     * Validates
     *
     * @param VersionInterface $version
     *
     * @return bool
     */
    public function isValid(VersionInterface $version)
    {
        $broken = $this->getBrokenSpecs($version);
        return empty($broken);
    }

    /**
     * Returns an array of broken specs and their error messages.
     *
     * @param VersionInterface $version
     *
     * @return array Keys must be specification class names and values must be error messages
     */
    public function getBrokenSpecs(VersionInterface $version)
    {
        $broken = [];
        foreach ($this->specs as $key => $spec) {
            if (!$spec->isSatisfiedBy($version)) {
                $broken[$key] = $spec->getErrorMessage();
            }
        }
        return $broken;
    }

    /**
     * Returns whether this validator has the specified spec loaded
     * @param string $specFQCN
     * @return bool
     */
    public function hasSpec($specFQCN)
    {
        return isset($this->specs[$specFQCN]);
    }
}
