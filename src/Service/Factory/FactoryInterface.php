<?php

namespace App\Service\Factory;

/**
 * Create interface for factory pattern.
 *
 * @template TEntityObject
 */
interface FactoryInterface
{
    /**
     * @return TEntityObject
     */
    public function build(): mixed;
}
