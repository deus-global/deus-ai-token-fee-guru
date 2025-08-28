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

use DeusGlobal\TokenFeeGuru\Exception\InvalidArgumentException;
use DeusGlobal\TokenFeeGuru\Exception\RuntimeException;

/**
 * CSV-based pricing data source.
 */
class CsvDataSource implements DataSourceInterface
{
    /** @var array */
    private $pricingData = [];
    
    /** @var string|null */
    private $filePath;
    
    /** @var bool */
    private $loaded = false;

    /**
     * @param string|null $filePath Path to CSV pricing file
     */
    public function __construct(?string $filePath = null)
    {
        $this->filePath = $filePath ?? $this->getDefaultPricingTablePath();
    }

    /**
     * Get available model names.
     *
     * @return array
     */
    public function getAvailableModels(): array
    {
        $this->ensureDataLoaded();
        return array_keys($this->pricingData);
    }

    /**
     * Get pricing data for all models.
     *
     * @return array
     */
    public function getPricingData(): array
    {
        $this->ensureDataLoaded();
        return $this->pricingData;
    }

    /**
     * Get pricing data for a specific model.
     *
     * @param string $modelName
     * @return array
     * @throws InvalidArgumentException
     */
    public function getModelPricing(string $modelName): array
    {
        $this->ensureDataLoaded();
        
        if (!isset($this->pricingData[$modelName])) {
            throw new InvalidArgumentException(sprintf('Model "%s" not found in pricing data', $modelName));
        }
        
        return $this->pricingData[$modelName];
    }

    /**
     * Check if a model exists in the data source.
     *
     * @param string $modelName
     * @return bool
     */
    public function hasModel(string $modelName): bool
    {
        $this->ensureDataLoaded();
        return isset($this->pricingData[$modelName]);
    }

    /**
     * Get the file path being used.
     *
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * Ensure pricing data is loaded.
     */
    private function ensureDataLoaded(): void
    {
        if (!$this->loaded) {
            $this->loadPricingData();
            $this->loaded = true;
        }
    }

    /**
     * Load pricing data from CSV file.
     *
     * @throws RuntimeException
     */
    private function loadPricingData(): void
    {
        if (!file_exists($this->filePath)) {
            throw new RuntimeException(sprintf('Pricing table CSV file not found: %s', $this->filePath));
        }

        if (!is_readable($this->filePath)) {
            throw new RuntimeException(sprintf('Pricing table CSV file is not readable: %s', $this->filePath));
        }

        $csvData = array_map('str_getcsv', file($this->filePath));
        if ($csvData === false) {
            throw new RuntimeException(sprintf('Failed to read pricing table CSV file: %s', $this->filePath));
        }

        $header = array_shift($csvData);
        
        foreach ($csvData as $rowIndex => $row) {
            if (count($row) < 6) {
                // Skip incomplete rows
                continue;
            }

            $modelApiName = trim($row[2]);
            if (empty($modelApiName)) {
                // Skip rows without model API name
                continue;
            }
            
            $inputTokenPriceValue = trim($row[3]);
            $cachedInputTokenPriceValue = trim($row[4]);
            $outputTokenPriceValue = trim($row[5]);
            
            $inputTokenPrice = $this->extractPriceFromString($inputTokenPriceValue);
            $cachedInputTokenPrice = $this->extractPriceFromString($cachedInputTokenPriceValue);
            $outputTokenPrice = $this->extractPriceFromString($outputTokenPriceValue);
            
            // Extract currency if available (column 6), default to USD
            $currency = (count($row) >= 7) ? trim($row[6]) : 'USD';

            $this->pricingData[$modelApiName] = [
                'vendor' => trim($row[0]),
                'model' => trim($row[1]),
                'model_api_name' => $modelApiName,
                'input_token_price' => $inputTokenPrice,
                'cached_input_token_price' => $cachedInputTokenPrice,
                'output_token_price' => $outputTokenPrice,
                'currency' => $currency,
                'raw_input_token_price_string' => $inputTokenPriceValue,
                'raw_cached_input_token_price_string' => $cachedInputTokenPriceValue,
                'raw_output_token_price_string' => $outputTokenPriceValue,
            ];
        }
    }

    /**
     * Extract numeric price from string.
     *
     * @param string $priceString
     * @return float
     */
    private function extractPriceFromString(string $priceString): float
    {
        // Handle formats: "$1.25", "$1.25 / 1M tokens", or plain "1.25"
        if (preg_match('/\$([0-9.]+)/', $priceString, $matches)) {
            return (float) $matches[1];
        }
        
        if (is_numeric($priceString)) {
            return (float) $priceString;
        }
        
        return 0.0;
    }

    /**
     * Get default pricing table path.
     *
     * @return string
     */
    private function getDefaultPricingTablePath(): string
    {
        $possiblePaths = [
            // From package root when installed via Composer
            __DIR__ . '/../../data/ai_token_pricing_table.csv',
            // From vendor directory when installed as dependency
            __DIR__ . '/../../../../../data/ai_token_pricing_table.csv',
            // From current working directory
            getcwd() . '/data/ai_token_pricing_table.csv',
            // From document root if set
            $_SERVER['DOCUMENT_ROOT'] . '/data/ai_token_pricing_table.csv',
            // Additional fallback paths
            __DIR__ . '/../../../../../../data/ai_token_pricing_table.csv',
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        // Return most likely path even if it doesn't exist
        return __DIR__ . '/../../data/ai_token_pricing_table.csv';
    }
}