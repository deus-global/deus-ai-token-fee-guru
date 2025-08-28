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

namespace DeusGlobal\TokenFeeGuru\Calculator;

use DeusGlobal\TokenFeeGuru\RequestOptions;
use DeusGlobal\TokenFeeGuru\DataSource\DataSourceInterface;
use DeusGlobal\TokenFeeGuru\Exception\InvalidArgumentException;

/**
 * Token cost calculator implementation.
 */
class TokenCalculator implements CalculatorInterface
{
    /** @var array */
    private $config;

    /**
     * @param array $config Calculator configuration
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'default_currency' => 'USD',
            'decimal_places' => 6,
        ], $config);
    }

    /**
     * Calculate token costs.
     *
     * @param RequestOptions      $options
     * @param DataSourceInterface $dataSource
     * @return array
     */
    public function calculate(RequestOptions $options, DataSourceInterface $dataSource): array
    {
        $modelName = $options->getModel();
        if (!$modelName) {
            throw new InvalidArgumentException('Model name is required');
        }

        try {
            $modelPricing = $dataSource->getModelPricing($modelName);
        } catch (\Exception $e) {
            throw new InvalidArgumentException(sprintf('Model "%s" not found in pricing data', $modelName));
        }

        $inputTokens = $options->getInputTokens();
        $outputTokens = $options->getOutputTokens();
        $cacheHitRate = $options->getCacheHitRate();
        $conversationRounds = $options->getConversationRounds();
        $userCount = $options->getUserCount();

        // Calculate token distribution
        $cachedInputTokens = $inputTokens * $cacheHitRate;
        $regularInputTokens = $inputTokens - $cachedInputTokens;

        // Calculate costs per million tokens
        $regularInputCost = ($regularInputTokens * $modelPricing['input_token_price']) / 1000000;
        $cachedInputCost = ($cachedInputTokens * $modelPricing['cached_input_token_price']) / 1000000;
        $outputCost = ($outputTokens * $modelPricing['output_token_price']) / 1000000;

        // Calculate scaled costs
        $costPerConversation = $regularInputCost + $cachedInputCost + $outputCost;
        $totalCostForAllConversations = $costPerConversation * $conversationRounds;
        $totalCostForAllUsers = $totalCostForAllConversations * $userCount;

        return [
            'model_name' => $modelName,
            'input_token_count' => $inputTokens,
            'output_token_count' => $outputTokens,
            'cache_hit_rate' => $cacheHitRate,
            'conversation_round_count' => $conversationRounds,
            'user_count' => $userCount,
            'token_breakdown' => [
                'cached_input_tokens' => $cachedInputTokens,
                'regular_input_tokens' => $regularInputTokens,
            ],
            'cost_breakdown' => [
                'regular_input_cost' => round($regularInputCost, $this->config['decimal_places']),
                'cached_input_cost' => round($cachedInputCost, $this->config['decimal_places']),
                'output_cost' => round($outputCost, $this->config['decimal_places']),
                'cost_per_conversation' => round($costPerConversation, $this->config['decimal_places']),
                'total_cost_for_all_conversations' => round($totalCostForAllConversations, $this->config['decimal_places']),
                'total_cost_for_all_users' => round($totalCostForAllUsers, $this->config['decimal_places']),
            ],
            'pricing_info' => $modelPricing,
        ];
    }

    /**
     * Compare costs across multiple models.
     *
     * @param array               $modelNames
     * @param RequestOptions      $options
     * @param DataSourceInterface $dataSource
     * @return array
     */
    public function compareModels(array $modelNames, RequestOptions $options, DataSourceInterface $dataSource): array
    {
        if (empty($modelNames)) {
            throw new InvalidArgumentException('Model names array cannot be empty');
        }

        $originalModel = $options->getModel();
        $comparisonResults = [];

        foreach ($modelNames as $modelName) {
            $options->setModel($modelName);
            
            try {
                $comparisonResults[$modelName] = $this->calculate($options, $dataSource);
            } catch (\Exception $e) {
                $comparisonResults[$modelName] = ['error' => $e->getMessage()];
            }
        }

        // Restore original model
        if ($originalModel !== null) {
            $options->setModel($originalModel);
        }

        return [
            'comparison_parameters' => $options->toArray(),
            'model_comparison_results' => $comparisonResults,
            'summary' => $this->generateComparisonSummary($comparisonResults),
        ];
    }

    /**
     * Generate a summary of the comparison results.
     *
     * @param array $results
     * @return array
     */
    private function generateComparisonSummary(array $results): array
    {
        $costs = [];
        $successful = [];

        foreach ($results as $modelName => $result) {
            if (isset($result['cost_breakdown']['total_cost_for_all_users'])) {
                $costs[$modelName] = $result['cost_breakdown']['total_cost_for_all_users'];
                $successful[$modelName] = $result;
            }
        }

        if (empty($costs)) {
            return ['status' => 'no_successful_calculations'];
        }

        $minCost = min($costs);
        $maxCost = max($costs);
        $mostEconomical = array_search($minCost, $costs, true);
        $mostExpensive = array_search($maxCost, $costs, true);

        return [
            'status' => 'success',
            'total_models_compared' => count($results),
            'successful_calculations' => count($successful),
            'most_economical' => [
                'model' => $mostEconomical,
                'total_cost' => $minCost,
            ],
            'most_expensive' => [
                'model' => $mostExpensive,
                'total_cost' => $maxCost,
            ],
            'potential_savings' => [
                'amount' => $maxCost - $minCost,
                'percentage' => $maxCost > 0 ? round((($maxCost - $minCost) / $maxCost) * 100, 2) : 0,
            ],
        ];
    }
}