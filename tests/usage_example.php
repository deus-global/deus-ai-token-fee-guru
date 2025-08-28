<?php

// Set up environment for testing
$currentWorkingDirectory = getcwd();
$_SERVER['DOCUMENT_ROOT'] = $currentWorkingDirectory;

// Load the package classes
require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

use DeusGlobal\TokenFeeGuru\Client;

echo "=== Deus AI Token Fee Calculator - Usage Examples ===\n\n";

try {
    // Initialize the calculator
    $calculator = new Client();

    echo "1. Basic Single Model Calculation\n";
    echo "=================================\n";
    
    $result = $calculator
        ->setModel('gpt-5-mini')
        ->setInputTokens(2000)
        ->setOutputTokens(1000)
        ->setCacheHitRate(0.1)
        ->setConversationRounds(1)
        ->setUserCount(1)
        ->calculate();

    echo "Model: {$result['model_name']}\n";
    echo "Total Cost: $" . number_format($result['cost_breakdown']['total_cost_for_all_users'], 6) . "\n";
    echo "Cost per Conversation: $" . number_format($result['cost_breakdown']['cost_per_conversation'], 6) . "\n\n";

    echo "2. Enterprise Scale Calculation\n";
    echo "==============================\n";
    
    $enterpriseResult = $calculator
        ->setModel('gpt-5')
        ->setInputTokens(5000)
        ->setOutputTokens(3000)
        ->setCacheHitRate(0.3)
        ->setConversationRounds(20)
        ->setUserCount(500)
        ->calculate();

    echo "Model: {$enterpriseResult['model_name']}\n";
    echo "Total Users: " . number_format($enterpriseResult['user_count']) . "\n";
    echo "Conversations per User: " . number_format($enterpriseResult['conversation_round_count']) . "\n";
    echo "Total Cost: $" . number_format($enterpriseResult['cost_breakdown']['total_cost_for_all_users'], 2) . "\n";
    echo "Cost per User: $" . number_format($enterpriseResult['cost_breakdown']['total_cost_for_all_conversations'], 4) . "\n\n";

    echo "3. Multiple Model Comparison\n";
    echo "===========================\n";
    
    $models = ['gpt-5', 'gpt-5-mini', 'gpt-5-nano'];
    $comparison = $calculator
        ->setInputTokens(1000)
        ->setOutputTokens(500)
        ->setCacheHitRate(0.2)
        ->setConversationRounds(10)
        ->setUserCount(100)
        ->compareModels($models);

    echo "Comparing models for 100 users, 10 conversations each:\n";
    foreach ($comparison['model_comparison_results'] as $modelName => $modelResult) {
        if (isset($modelResult['cost_breakdown'])) {
            $totalCost = $modelResult['cost_breakdown']['total_cost_for_all_users'];
            echo "- {$modelName}: $" . number_format($totalCost, 2) . "\n";
        } else {
            echo "- {$modelName}: Error - " . $modelResult['error'] . "\n";
        }
    }
    echo "\n";

    echo "4. Available Models\n";
    echo "==================\n";
    $availableModels = $calculator->getAvailableModels();
    foreach ($availableModels as $model) {
        echo "- $model\n";
    }
    echo "\nTotal models available: " . count($availableModels) . "\n\n";

    echo "5. Pricing Data Overview\n";
    echo "=======================\n";
    $pricingData = $calculator->getPricingData();
    foreach ($pricingData as $modelName => $pricing) {
        echo "{$modelName} ({$pricing['vendor']}):\n";
        echo "  Input: \${$pricing['input_token_price']}/1M tokens\n";
        echo "  Cached: \${$pricing['cached_input_token_price']}/1M tokens\n";
        echo "  Output: \${$pricing['output_token_price']}/1M tokens\n\n";
    }

    echo "6. Cache Hit Rate Impact Analysis\n";
    echo "================================\n";
    
    $cacheRates = [0.0, 0.1, 0.3, 0.5, 0.7, 0.9];
    echo "Cache hit rate impact for gpt-5-mini (1000 input, 500 output tokens):\n";
    
    foreach ($cacheRates as $cacheRate) {
        $cacheResult = $calculator
            ->setModel('gpt-5-mini')
            ->setInputTokens(1000)
            ->setOutputTokens(500)
            ->setCacheHitRate($cacheRate)
            ->setConversationRounds(1)
            ->setUserCount(1)
            ->calculate();
        
        $cost = $cacheResult['cost_breakdown']['cost_per_conversation'];
        echo "- " . ($cacheRate * 100) . "% cache rate: $" . number_format($cost, 6) . "\n";
    }
    
    echo "\n✅ All examples executed successfully!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Make sure the pricing data file exists at: data/ai_token_pricing_table.csv\n";
}