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

namespace DeusGlobal\TokenFeeGuru\Tests\Integration;

use DeusGlobal\TokenFeeGuru\Client;
use PHPUnit\Framework\TestCase;

class FullCalculationTest extends TestCase
{
    /** @var Client */
    private $client;

    protected function setUp(): void
    {
        $this->client = new Client([
            'pricing_table_path' => __DIR__ . '/../../data/ai_token_pricing_table.csv'
        ]);
    }

    public function testCompleteWorkflowBasicCalculation(): void
    {
        // Test the complete workflow from configuration to calculation
        $result = $this->client
            ->setModel('gpt-5-mini')
            ->setInputTokens(2000)
            ->setOutputTokens(1000)
            ->setConversationRounds(1)
            ->setUserCount(1)
            ->calculate();

        // Verify all expected structure is present
        $this->assertArrayHasKey('model_name', $result);
        $this->assertArrayHasKey('input_token_count', $result);
        $this->assertArrayHasKey('output_token_count', $result);
        $this->assertArrayHasKey('cache_hit_rate', $result);
        $this->assertArrayHasKey('conversation_round_count', $result);
        $this->assertArrayHasKey('user_count', $result);
        $this->assertArrayHasKey('token_breakdown', $result);
        $this->assertArrayHasKey('cost_breakdown', $result);
        $this->assertArrayHasKey('pricing_info', $result);

        // Verify values
        $this->assertEquals('gpt-5-mini', $result['model_name']);
        $this->assertEquals(2000, $result['input_token_count']);
        $this->assertEquals(1000, $result['output_token_count']);
        $this->assertEquals(0.0, $result['cache_hit_rate']);

        // Verify cost calculation is reasonable
        $this->assertGreaterThan(0, $result['cost_breakdown']['total_cost_for_all_users']);
    }

    public function testCompleteWorkflowWithCaching(): void
    {
        $result = $this->client
            ->setModel('gpt-5-mini')
            ->setInputTokens(5000)
            ->setOutputTokens(2000)
            ->setCacheHitRate(0.3)
            ->setConversationRounds(10)
            ->setUserCount(50)
            ->calculate();

        // Verify cache calculations
        $expectedCachedTokens = 5000 * 0.3; // 1500
        $expectedRegularTokens = 5000 - $expectedCachedTokens; // 3500

        $this->assertEquals($expectedCachedTokens, $result['token_breakdown']['cached_input_tokens']);
        $this->assertEquals($expectedRegularTokens, $result['token_breakdown']['regular_input_tokens']);

        // Verify scaling
        $this->assertEquals(10, $result['conversation_round_count']);
        $this->assertEquals(50, $result['user_count']);

        // Cost should be scaled appropriately
        $costPerConversation = $result['cost_breakdown']['cost_per_conversation'];
        $totalForAllConversations = $result['cost_breakdown']['total_cost_for_all_conversations'];
        $totalForAllUsers = $result['cost_breakdown']['total_cost_for_all_users'];

        $this->assertEquals($costPerConversation * 10, $totalForAllConversations);
        $this->assertEquals($totalForAllConversations * 50, $totalForAllUsers);
    }

    public function testCompleteModelComparison(): void
    {
        $models = ['gpt-5-mini', 'gpt-5-nano', 'gpt-5'];
        
        $result = $this->client
            ->setInputTokens(1000)
            ->setOutputTokens(500)
            ->setCacheHitRate(0.2)
            ->setConversationRounds(5)
            ->setUserCount(10)
            ->compareModels($models);

        // Verify structure
        $this->assertArrayHasKey('comparison_parameters', $result);
        $this->assertArrayHasKey('model_comparison_results', $result);
        $this->assertArrayHasKey('summary', $result);

        // Verify all models are present
        foreach ($models as $model) {
            $this->assertArrayHasKey($model, $result['model_comparison_results']);
        }

        // Verify summary contains analysis
        $summary = $result['summary'];
        $this->assertEquals('success', $summary['status']);
        $this->assertArrayHasKey('most_economical', $summary);
        $this->assertArrayHasKey('most_expensive', $summary);
        $this->assertArrayHasKey('potential_savings', $summary);

        // Verify most economical is cheaper than most expensive
        $mostEconomicalCost = $summary['most_economical']['total_cost'];
        $mostExpensiveCost = $summary['most_expensive']['total_cost'];
        $this->assertLessThanOrEqual($mostExpensiveCost, $mostEconomicalCost);
    }

    public function testChainedOperations(): void
    {
        // Test that the client can be used for multiple operations
        $firstResult = $this->client
            ->setModel('gpt-5-mini')
            ->setInputTokens(1000)
            ->setOutputTokens(500)
            ->calculate();

        $this->assertEquals('gpt-5-mini', $firstResult['model_name']);

        // Reset and use different parameters
        $secondResult = $this->client
            ->reset()
            ->setModel('gpt-5-nano')
            ->setInputTokens(2000)
            ->setOutputTokens(1000)
            ->setCacheHitRate(0.5)
            ->calculate();

        $this->assertEquals('gpt-5-nano', $secondResult['model_name']);
        $this->assertEquals(2000, $secondResult['input_token_count']);
        $this->assertEquals(0.5, $secondResult['cache_hit_rate']);
    }

    public function testAvailableModelsIntegration(): void
    {
        $models = $this->client->getAvailableModels();
        
        $this->assertIsArray($models);
        $this->assertNotEmpty($models);
        
        // Test that we can calculate with each available model
        foreach (array_slice($models, 0, 3) as $model) { // Test first 3 models
            $result = $this->client
                ->reset()
                ->setModel($model)
                ->setInputTokens(100)
                ->setOutputTokens(50)
                ->calculate();
            
            $this->assertEquals($model, $result['model_name']);
            $this->assertArrayHasKey('cost_breakdown', $result);
        }
    }

    public function testPricingDataIntegration(): void
    {
        $pricingData = $this->client->getPricingData();
        
        $this->assertIsArray($pricingData);
        $this->assertNotEmpty($pricingData);
        
        // Verify each pricing entry has required fields
        foreach ($pricingData as $modelName => $pricing) {
            $this->assertArrayHasKey('vendor', $pricing);
            $this->assertArrayHasKey('model', $pricing);
            $this->assertArrayHasKey('model_api_name', $pricing);
            $this->assertArrayHasKey('input_token_price', $pricing);
            $this->assertArrayHasKey('cached_input_token_price', $pricing);
            $this->assertArrayHasKey('output_token_price', $pricing);
            
            // Verify prices are numeric and reasonable
            $this->assertIsNumeric($pricing['input_token_price']);
            $this->assertIsNumeric($pricing['cached_input_token_price']);
            $this->assertIsNumeric($pricing['output_token_price']);
            $this->assertGreaterThanOrEqual(0, $pricing['input_token_price']);
            $this->assertGreaterThanOrEqual(0, $pricing['cached_input_token_price']);
            $this->assertGreaterThanOrEqual(0, $pricing['output_token_price']);
        }
    }
}