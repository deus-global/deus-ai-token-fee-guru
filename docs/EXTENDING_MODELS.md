# Extending Model Support

This guide explains how to add support for new LLM models to the Deus AI Token Fee Guru package.

## Overview

The package uses a CSV file (`data/ai_token_pricing_table.csv`) to store pricing information for different LLM models. You can easily extend support to additional models by adding their pricing data to this file.

## CSV Format Specification

The CSV file has the following column structure:

| Column | Description | Example | Required |
|--------|-------------|---------|----------|
| `vendor` | Model provider/company | `OpenAI`, `Anthropic`, `Google` | Yes |
| `model` | Human-readable model name | `GPT-5 mini`, `Claude 3.5 Sonnet` | Yes |
| `model_api_name` | API identifier used in code | `gpt-5-mini`, `claude-3-5-sonnet-20241022` | Yes |
| `input_token_price` | Cost per 1M input tokens | `1.25`, `3.00` | Yes |
| `cached_input_token_price` | Cost per 1M cached input tokens | `0.13`, `0.30` | Yes |
| `output_token_price` | Cost per 1M output tokens | `10.00`, `15.00` | Yes |
| `currency` | Currency code | `USD`, `EUR` | Yes |

## Adding New Models

### Step 1: Get Pricing Information

Collect the following pricing information from the model provider:
- Input token cost per 1 million tokens
- Cached input token cost per 1 million tokens (usually 90% cheaper)
- Output token cost per 1 million tokens

### Step 2: Add to CSV File

Edit `data/ai_token_pricing_table.csv` and add a new row:

```csv
vendor,model,model_api_name,input_token_price,cached_input_token_price,output_token_price,currency
# Existing models...
OpenAI,GPT-5,gpt-5,1.25,0.13,10.00,USD
OpenAI,GPT-5 mini,gpt-5-mini,0.25,0.03,2.00,USD
# New models...
Anthropic,Claude 3.5 Sonnet,claude-3-5-sonnet-20241022,3.00,0.30,15.00,USD
Anthropic,Claude 3.5 Haiku,claude-3-5-haiku-20241022,0.25,0.025,1.25,USD
Google,Gemini 1.5 Pro,gemini-1.5-pro,1.25,0.125,5.00,USD
Google,Gemini 1.5 Flash,gemini-1.5-flash,0.075,0.0075,0.30,USD
Cohere,Command R+,command-r-plus,3.00,0.30,15.00,USD
```

### Step 3: Verify the Addition

Test that the new models are recognized:

```bash
# List all available models
./vendor/bin/deus-token-calculator --list-available-models

# Test calculation with new model
./vendor/bin/deus-token-calculator --model=claude-3-5-sonnet-20241022 --input-token-count=1000 --output-token-count=500
```

## Examples by Provider

### Anthropic Claude Models

```csv
Anthropic,Claude 3.5 Sonnet,claude-3-5-sonnet-20241022,3.00,0.30,15.00,USD
Anthropic,Claude 3.5 Haiku,claude-3-5-haiku-20241022,0.25,0.025,1.25,USD
Anthropic,Claude 3 Opus,claude-3-opus-20240229,15.00,1.50,75.00,USD
```

### Google Gemini Models

```csv
Google,Gemini 1.5 Pro,gemini-1.5-pro,1.25,0.125,5.00,USD
Google,Gemini 1.5 Flash,gemini-1.5-flash,0.075,0.0075,0.30,USD
Google,Gemini 1.0 Pro,gemini-1.0-pro,0.50,0.05,1.50,USD
```

### Cohere Models

```csv
Cohere,Command R+,command-r-plus,3.00,0.30,15.00,USD
Cohere,Command R,command-r,0.50,0.05,1.50,USD
Cohere,Command,command,1.00,0.10,2.00,USD
```

### Mistral Models

```csv
Mistral,Mistral Large,mistral-large-latest,4.00,0.40,12.00,USD
Mistral,Mistral Medium,mistral-medium-latest,2.70,0.27,8.10,USD
Mistral,Mistral Small,mistral-small-latest,1.00,0.10,3.00,USD
```

## Usage Examples

### PHP API

```php
use DeusGlobal\TokenFeeGuru\Client;

$client = new Client();

// Calculate cost for Claude 3.5 Sonnet
$result = $client
    ->setModel('claude-3-5-sonnet-20241022')
    ->setInputTokens(2000)
    ->setOutputTokens(1000)
    ->setCacheHitRate(0.2)
    ->calculate();

echo "Claude 3.5 Sonnet cost: $" . $result['cost_breakdown']['total_cost_for_all_users'];

// Compare across different providers
$comparison = $client
    ->setInputTokens(1000)
    ->setOutputTokens(500)
    ->compareModels([
        'gpt-5-mini',           // OpenAI
        'claude-3-5-haiku-20241022',  // Anthropic
        'gemini-1.5-flash',     // Google
        'command-r'             // Cohere
    ]);

foreach ($comparison['model_comparison_results'] as $model => $result) {
    if (isset($result['cost_breakdown'])) {
        echo "{$model}: $" . number_format($result['cost_breakdown']['total_cost_for_all_users'], 6) . "\n";
    }
}
```

### CLI Usage

```bash
# List all models (including newly added)
./vendor/bin/deus-token-calculator --list-available-models

# Calculate with Anthropic model
./vendor/bin/deus-token-calculator \
  --model=claude-3-5-sonnet-20241022 \
  --input-token-count=1000 \
  --output-token-count=500 \
  --cache-hit-rate=0.3

# Compare models from different providers
./vendor/bin/deus-token-calculator \
  --compare-models=gpt-5-mini,claude-3-5-haiku-20241022,gemini-1.5-flash \
  --input-token-count=1000 \
  --output-token-count=500 \
  --output-format=json

# Use external pricing file
./vendor/bin/deus-token-calculator \
  --pricing-table-path=/path/to/my_models.csv \
  --list-available-models
```

## Custom Pricing Files

You can also create separate CSV files for different scenarios:

### Enterprise Pricing File
```csv
# enterprise_models.csv - Custom enterprise rates
vendor,model,model_api_name,input_token_price,cached_input_token_price,output_token_price,currency
OpenAI,GPT-5 Enterprise,gpt-5-enterprise,1.00,0.10,8.00,USD
Anthropic,Claude 3.5 Enterprise,claude-3.5-enterprise,2.50,0.25,12.00,USD
```

### Regional Pricing File
```csv
# eu_pricing.csv - European pricing
vendor,model,model_api_name,input_token_price,cached_input_token_price,output_token_price,currency
OpenAI,GPT-5,gpt-5,1.15,0.115,9.20,EUR
Anthropic,Claude 3.5,claude-3-5-sonnet-20241022,2.75,0.275,13.75,EUR
```

## Best Practices

1. **Keep Pricing Updated**: Model pricing changes frequently. Review and update regularly.

2. **Use Consistent Naming**: Follow the provider's official API naming conventions.

3. **Document Cache Rates**: If a model doesn't support caching, set cached price same as regular price.

4. **Test New Models**: Always test new models after adding them:
   ```bash
   ./vendor/bin/deus-token-calculator --model=new-model-name --input-token-count=100 --output-token-count=50
   ```

5. **Backup Original File**: Keep a backup of the original CSV before making changes.

6. **Version Control**: Track changes to your pricing files in version control.

## Troubleshooting

### Model Not Found
```
Error: Model "model-name" not found in pricing data
```
- Check the `model_api_name` matches exactly what you added to CSV
- Ensure CSV file is valid (no missing commas, quotes properly escaped)

### Invalid Pricing Data
```
Error: Failed to read pricing table CSV file
```
- Verify CSV format is correct
- Check file permissions
- Ensure no special characters in file path

### Cache Rate Issues
- If a model doesn't support caching, set `cached_input_token_price` same as `input_token_price`
- Cache rates should be between 0.0 and 1.0

## Getting Help

- Check the main README for more examples
- Review existing CSV entries for format reference
- Test with known working models first
- Use `--list-available-models` to verify models are loaded correctly