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

namespace Baleen\Migrations\Delta\Collection;

use Baleen\Migrations\Common\Collection\CollectionInterface;
use Baleen\Migrations\Delta\DeltaId;
use Baleen\Migrations\Delta\DeltaInterface;

/**
 * Interface ResolvableCollectionInterface
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
interface ResolvableCollectionInterface extends CollectionInterface
{
    /**
     * Searches for an element using different methods based on the argument type.
     *
     * @param string|DeltaId|DeltaInterface $element
     *
     * @return DeltaInterface|null
     */
    public function find($element);

    /**
     * Uses find() to determine if the specified element exists in the collection.
     *
     * @param string|DeltaId|DeltaInterface $element
     *
     * @return bool
     */
    public function has($element);
}
