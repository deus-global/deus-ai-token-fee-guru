<?php

require_once 'vendor/autoload.php';

use DeusGlobal\TokenFeeGuru\Client;

echo "=== Testing Model Extension Documentation ===\n\n";

// Test the current CSV file
$client = new Client();

echo "1. Current Available Models:\n";
$models = $client->getAvailableModels();
foreach ($models as $model) {
    echo "  • $model\n";
}

echo "\n2. Testing calculation with existing model:\n";
$result = $client
    ->setModel('gpt-5-mini')
    ->setInputTokens(1000)
    ->setOutputTokens(500)
    ->calculate();

echo "  Model: " . $result['model_name'] . "\n";
echo "  Cost: $" . number_format($result['cost_breakdown']['total_cost_for_all_users'], 6) . "\n";

echo "\n3. Pricing Data Structure:\n";
$pricingData = $client->getPricingData();
$firstModel = array_key_first($pricingData);
echo "  Example pricing structure for '$firstModel':\n";
foreach ($pricingData[$firstModel] as $key => $value) {
    echo "    $key: $value\n";
}

echo "\n✅ Extension documentation ready!\n";
echo "📖 See docs/EXTENDING_MODELS.md for detailed instructions.\n";

?>