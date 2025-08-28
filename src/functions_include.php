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

namespace DeusGlobal\TokenFeeGuru;

/**
 * Utility functions for token cost calculations.
 */

if (!function_exists('DeusGlobal\TokenFeeGuru\calculate_tokens')) {
    /**
     * Quick utility function to calculate token costs for a model.
     *
     * @param string $modelName
     * @param int    $inputTokens
     * @param int    $outputTokens
     * @param float  $cacheHitRate
     * @param string|null $pricingTablePath
     *
     * @return array
     */
    function calculate_tokens(
        string $modelName,
        int $inputTokens,
        int $outputTokens,
        float $cacheHitRate = 0.0,
        ?string $pricingTablePath = null
    ): array {
        $calculator = new Client($pricingTablePath);
        
        return $calculator
            ->setModel($modelName)
            ->setInputTokens($inputTokens)
            ->setOutputTokens($outputTokens)
            ->setCacheHitRate($cacheHitRate)
            ->calculate();
    }
}

if (!function_exists('DeusGlobal\TokenFeeGuru\format_cost')) {
    /**
     * Format cost value for display.
     *
     * @param float  $cost
     * @param string $currency
     * @param int    $decimals
     *
     * @return string
     */
    function format_cost(float $cost, string $currency = 'USD', int $decimals = 6): string
    {
        return sprintf('$%s %s', number_format($cost, $decimals), $currency);
    }
}