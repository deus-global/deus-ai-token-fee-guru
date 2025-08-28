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

use DeusGlobal\TokenFeeGuru\Calculator\CalculatorInterface;
use DeusGlobal\TokenFeeGuru\Calculator\TokenCalculator;
use DeusGlobal\TokenFeeGuru\DataSource\DataSourceInterface;
use DeusGlobal\TokenFeeGuru\DataSource\CsvDataSource;
use DeusGlobal\TokenFeeGuru\Exception\InvalidArgumentException;

/**
 * Main client for token fee calculations.
 */
class Client implements ClientInterface
{
    /** @var CalculatorInterface */
    private $calculator;
    
    /** @var DataSourceInterface */
    private $dataSource;
    
    /** @var array */
    private $config;
    
    /** @var RequestOptions */
    private $options;

    /**
     * @param array $config Client configuration options
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'pricing_table_path' => null,
            'default_currency' => 'USD',
            'decimal_places' => 6,
        ], $config);
        
        $this->options = new RequestOptions();
        $this->initializeDataSource();
        $this->initializeCalculator();
    }

    /**
     * Set the model name for calculations.
     *
     * @param string $modelName
     * @return ClientInterface
     */
    public function setModel(string $modelName): ClientInterface
    {
        $this->options->setModel($modelName);
        return $this;
    }

    /**
     * Set the number of input tokens.
     *
     * @param int $tokens
     * @return ClientInterface
     */
    public function setInputTokens(int $tokens): ClientInterface
    {
        $this->options->setInputTokens($tokens);
        return $this;
    }

    /**
     * Set the number of output tokens.
     *
     * @param int $tokens
     * @return ClientInterface
     */
    public function setOutputTokens(int $tokens): ClientInterface
    {
        $this->options->setOutputTokens($tokens);
        return $this;
    }

    /**
     * Set the cache hit rate (0.0 to 1.0).
     *
     * @param float $rate
     * @return ClientInterface
     */
    public function setCacheHitRate(float $rate): ClientInterface
    {
        if ($rate < 0.0 || $rate > 1.0) {
            throw new InvalidArgumentException('Cache hit rate must be between 0.0 and 1.0');
        }
        
        $this->options->setCacheHitRate($rate);
        return $this;
    }

    /**
     * Set the number of conversation rounds.
     *
     * @param int $rounds
     * @return ClientInterface
     */
    public function setConversationRounds(int $rounds): ClientInterface
    {
        $this->options->setConversationRounds($rounds);
        return $this;
    }

    /**
     * Set the number of users.
     *
     * @param int $users
     * @return ClientInterface
     */
    public function setUserCount(int $users): ClientInterface
    {
        $this->options->setUserCount($users);
        return $this;
    }

    /**
     * Calculate token costs with current options.
     *
     * @return array
     */
    public function calculate(): array
    {
        return $this->calculator->calculate($this->options, $this->dataSource);
    }

    /**
     * Calculate costs for multiple models.
     *
     * @param array $modelNames
     * @return array
     */
    public function compareModels(array $modelNames): array
    {
        return $this->calculator->compareModels($modelNames, $this->options, $this->dataSource);
    }

    /**
     * Get available models.
     *
     * @return array
     */
    public function getAvailableModels(): array
    {
        return $this->dataSource->getAvailableModels();
    }

    /**
     * Get pricing data for all models.
     *
     * @return array
     */
    public function getPricingData(): array
    {
        return $this->dataSource->getPricingData();
    }

    /**
     * Get pricing data for a specific model.
     *
     * @param string $modelName
     * @return array
     */
    public function getModelPricing(string $modelName): array
    {
        return $this->dataSource->getModelPricing($modelName);
    }

    /**
     * Reset all calculation options to defaults.
     *
     * @return ClientInterface
     */
    public function reset(): ClientInterface
    {
        $this->options = new RequestOptions();
        return $this;
    }

    /**
     * Get current configuration.
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Initialize the data source based on configuration.
     */
    private function initializeDataSource(): void
    {
        $pricingTablePath = $this->config['pricing_table_path'] ?? null;
        $this->dataSource = new CsvDataSource($pricingTablePath);
    }

    /**
     * Initialize the calculator.
     */
    private function initializeCalculator(): void
    {
        $this->calculator = new TokenCalculator([
            'default_currency' => $this->config['default_currency'],
            'decimal_places' => $this->config['decimal_places'],
        ]);
    }
}