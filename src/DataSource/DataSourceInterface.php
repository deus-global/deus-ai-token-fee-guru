<?php

/**
 * This file is part of the Deus Global Token Fee Guru library.
 *
 * (c) Deus Global <info@deus.global>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace DeusGlobal\TokenFeeGuru\DataSource;

/**
 * Interface for pricing data sources.
 */
interface DataSourceInterface
{
    /**
     * Get available model names.
     *
     * @return array
     */
    public function getAvailableModels(): array;

    /**
     * Get pricing data for all models.
     *
     * @return array
     */
    public function getPricingData(): array;

    /**
     * Get pricing data for a specific model.
     *
     * @param string $modelName
     * @return array
     * @throws \DeusGlobal\TokenFeeGuru\Exception\InvalidArgumentException
     */
    public function getModelPricing(string $modelName): array;

    /**
     * Check if a model exists in the data source.
     *
     * @param string $modelName
     * @return bool
     */
    public function hasModel(string $modelName): bool;
}