# Deus AI Token Fee Guru

[![Packagist Version](https://img.shields.io/packagist/v/deus-global/deus-ai-token-fee-guru)](https://packagist.org/packages/deus-global/deus-ai-token-fee-guru)
[![PHP Version](https://img.shields.io/packagist/php-v/deus-global/deus-ai-token-fee-guru)](https://packagist.org/packages/deus-global/deus-ai-token-fee-guru)
[![License](https://img.shields.io/packagist/l/deus-global/deus-ai-token-fee-guru)](https://github.com/deus-global/deus-ai-token-fee-guru/blob/main/LICENSE)

A comprehensive PHP package for calculating Large Language Model (LLM) token usage costs with support for cached tokens, multiple users, conversation rounds, and model comparison.

## Features

- **Multi-Model Support**: Calculate costs for different LLM models (GPT-5, GPT-4.1, etc.)
- **Cache-Aware Calculations**: Factor in prompt cache hit rates for accurate cost estimates  
- **Enterprise Scale**: Support for multi-user scenarios and conversation rounds
- **Multiple Output Formats**: Plain text, JSON, and Markdown exports
- **Multi-Language Support**: English (en-US) and Traditional Chinese (zh-TW)
- **Custom Pricing Tables**: Use your own CSV pricing data
- **Extensible Model Support**: Easily add new LLM models via CSV configuration
- **CLI Interface**: Interactive and command-line modes
- **Chainable API**: Fluent interface with method chaining
- **PSR-4 Autoloading**: Modern PHP package standards

## Installation

### Via Composer (Recommended)

```bash
composer require deus-global/deus-ai-token-fee-guru
```

## Quick Start

### CLI Usage

After installing via Composer, the CLI tool is available in your vendor/bin directory:

```bash
# List available models
./vendor/bin/deus-token-calculator --list-available-models

# Basic calculation
./vendor/bin/deus-token-calculator --model=gpt-5-mini --input-token-count=2000 --output-token-count=1000

# Enterprise scenario with cache
./vendor/bin/deus-token-calculator --model=gpt-5 --input-token-count=5000 --output-token-count=3000 --cache-hit-rate=0.3 --conversation-rounds=20 --user-count=500

# Compare multiple models
./vendor/bin/deus-token-calculator --compare-models=gpt-5,gpt-5-mini,gpt-5-nano --input-token-count=1000 --output-token-count=500

# Interactive mode
./vendor/bin/deus-token-calculator --interactive-mode

# Export as JSON
./vendor/bin/deus-token-calculator --model=gpt-5-mini --input-token-count=1000 --output-token-count=500 --output-format=json

# Use Composer scripts
composer cli -- --model=gpt-5-mini --input-token-count=1000 --output-token-count=500
```

### PHP API Usage

```php
require_once 'vendor/autoload.php';

use DeusGlobal\TokenFeeGuru\Client;

$client = new Client();

$result = $client
    ->setModel('gpt-5-mini')
    ->setInputTokens(2000)
    ->setOutputTokens(1000)
    ->setCacheHitRate(0.1)
    ->setConversationRounds(1)
    ->setUserCount(1)
    ->calculate();

echo "Total Cost: $" . $result['cost_breakdown']['total_cost_for_all_users'];
```

### Configuration

The client accepts configuration options during instantiation:

```php
$client = new Client([
    'pricing_table_path' => '/path/to/custom/pricing.csv',
    'default_currency' => 'USD',
    'decimal_places' => 6,
]);
```

### Advanced Usage

#### Enterprise-scale calculations

```php
$result = $client
    ->setModel('gpt-5')
    ->setInputTokens(5000)
    ->setOutputTokens(3000)
    ->setCacheHitRate(0.3)  // 30% cache hit rate
    ->setConversationRounds(20)
    ->setUserCount(500)
    ->calculate();

echo "Enterprise Cost: $" . number_format($result['cost_breakdown']['total_cost_for_all_users'], 2);
```

#### Model comparison

```php
$comparison = $client
    ->setInputTokens(1000)
    ->setOutputTokens(500)
    ->setCacheHitRate(0.2)
    ->compareModels(['gpt-5', 'gpt-5-mini', 'gpt-5-nano']);

foreach ($comparison['model_comparison_results'] as $model => $result) {
    if (isset($result['cost_breakdown'])) {
        $cost = $result['cost_breakdown']['total_cost_for_all_users'];
        echo "{$model}: $" . number_format($cost, 6) . "\n";
    }
}

// Access comparison summary
$summary = $comparison['summary'];
echo "Most economical: " . $summary['most_economical']['model'] . "\n";
echo "Potential savings: $" . number_format($summary['potential_savings']['amount'], 6) . "\n";
```

#### Using utility functions

```php
use function DeusGlobal\TokenFeeGuru\calculate_tokens;
use function DeusGlobal\TokenFeeGuru\format_cost;

// Quick calculation
$result = calculate_tokens('gpt-5-mini', 1000, 500, 0.1);
echo format_cost($result['cost_breakdown']['total_cost_for_all_users']);
```

### Composer Scripts

After installation, you can use these convenient scripts:

```bash
# Run usage examples
composer examples

# Launch CLI (with arguments)
composer cli -- --list-available-models

# Run tests (when available)
composer test
```


## Pricing Data Format

The system uses CSV files for pricing data. Default format:

```csv
vendor,model,model_api_name,input_token_price,cached_input_token_price,output_token_price,currency
OpenAI,GPT-5,gpt-5,1.25,0.13,10.00,USD
OpenAI,GPT-5 mini,gpt-5-mini,0.25,0.03,2.00,USD
OpenAI,GPT-5 nano,gpt-5-nano,0.05,0.01,0.40,USD
```

All prices are per 1 million tokens (as per PRD requirements).

### Adding Custom Models

You can easily extend support to additional LLM models by adding their pricing data to `data/ai_token_pricing_table.csv`:

#### CSV Column Specification:
- **vendor**: Model provider (e.g., "OpenAI", "Anthropic", "Google")  
- **model**: Human-readable model name (e.g., "Claude 3.5 Sonnet")
- **model_api_name**: API identifier used in code (e.g., "claude-3-5-sonnet-20241022")
- **input_token_price**: Cost per 1M input tokens (e.g., "3.00")
- **cached_input_token_price**: Cost per 1M cached input tokens (e.g., "0.30")
- **output_token_price**: Cost per 1M output tokens (e.g., "15.00")
- **currency**: Currency code (e.g., "USD")

#### Example: Adding Claude 3.5 Sonnet

```csv
vendor,model,model_api_name,input_token_price,cached_input_token_price,output_token_price,currency
OpenAI,GPT-5,gpt-5,1.25,0.13,10.00,USD
OpenAI,GPT-5 mini,gpt-5-mini,0.25,0.03,2.00,USD
Anthropic,Claude 3.5 Sonnet,claude-3-5-sonnet-20241022,3.00,0.30,15.00,USD
Google,Gemini 1.5 Pro,gemini-1.5-pro,1.25,0.13,5.00,USD
```

#### Usage with Custom Models:

```php
$client = new Client();

// Use newly added models
$result = $client
    ->setModel('claude-3-5-sonnet-20241022')
    ->setInputTokens(1000)
    ->setOutputTokens(500)
    ->calculate();

// Compare across different vendors
$comparison = $client
    ->compareModels([
        'gpt-5-mini',
        'claude-3-5-sonnet-20241022', 
        'gemini-1.5-pro'
    ]);
```

#### CLI Usage with Custom Models:

```bash
# List all models (including newly added ones)
./vendor/bin/deus-token-calculator --list-available-models

# Calculate with custom model
./vendor/bin/deus-token-calculator --model=claude-3-5-sonnet-20241022 --input-token-count=1000 --output-token-count=500

# Compare custom models
./vendor/bin/deus-token-calculator --compare-models=gpt-5-mini,claude-3-5-sonnet-20241022 --input-token-count=1000 --output-token-count=500
```

#### Using External Pricing Files:

```php
// Load custom pricing data from different location
$client = new Client([
    'pricing_table_path' => '/path/to/your/custom_pricing.csv'
]);

// Or via CLI
./vendor/bin/deus-token-calculator --pricing-table-path=/path/to/custom_pricing.csv --list-available-models
```

**💡 Tip**: Keep your CSV file updated with the latest pricing from model providers to ensure accurate cost calculations.

**📖 For detailed model extension guide**: See [docs/EXTENDING_MODELS.md](docs/EXTENDING_MODELS.md) for comprehensive instructions and examples.

## CLI Parameters

### Core Parameters
- `--model=MODEL` - Model name (e.g., gpt-5-mini)
- `--input-token-count=NUM` - Input tokens per conversation
- `--output-token-count=NUM` - Output tokens per conversation
- `--cache-hit-rate=RATE` - Cache hit rate (0.0 to 1.0)
- `--conversation-rounds=NUM` - Conversations per user
- `--user-count=NUM` - Number of users

### Output Options
- `--output-format=FORMAT` - text, json, or markdown
- `--output-file=FILE` - Save to file
- `--language=LANG` - en-US or zh-TW

### Advanced Features
- `--compare-models=MODELS` - Compare multiple models (comma-separated)
- `--pricing-table-path=PATH` - Custom pricing CSV file
- `--interactive-mode` - Interactive CLI session
- `--list-available-models` - Show available models
- `--show-pricing-data` - Show detailed pricing info

## Complete CLI Examples

### Basic Usage Examples

#### Simple Single Model Calculation
```bash
# Minimal calculation
./vendor/bin/deus-token-calculator --model=gpt-5-mini --input-token-count=1000 --output-token-count=500

# With cache hit rate
./vendor/bin/deus-token-calculator --model=gpt-5-mini --input-token-count=2000 --output-token-count=1000 --cache-hit-rate=0.1
```

#### Multi-User Scenarios
```bash
# Small team (10 users, 5 conversations each)
./vendor/bin/deus-token-calculator --model=gpt-5 --input-token-count=3000 --output-token-count=1500 --user-count=10 --conversation-rounds=5

# Enterprise scale (1000 users, 50 conversations each)
./vendor/bin/deus-token-calculator --model=gpt-5-mini --input-token-count=5000 --output-token-count=2000 --cache-hit-rate=0.3 --conversation-rounds=50 --user-count=1000
```

### Information & Discovery

#### List Available Models
```bash
# Show all available models
./vendor/bin/deus-token-calculator --list-available-models

# With custom pricing table
./vendor/bin/deus-token-calculator --list-available-models --pricing-table-path=/path/to/custom_pricing.csv
```

#### Show Pricing Information
```bash
# Display detailed pricing data
./vendor/bin/deus-token-calculator --show-pricing-data

# In different languages
./vendor/bin/deus-token-calculator --show-pricing-data --language=zh-TW
./vendor/bin/deus-token-calculator --show-pricing-data --language=en-US
```

#### Help and Usage
```bash
# Show help information
./vendor/bin/deus-token-calculator --help
./vendor/bin/deus-token-calculator -h
```

### Model Comparison Examples

#### Compare All GPT-5 Models
```bash
# Basic comparison
./vendor/bin/deus-token-calculator --compare-models=gpt-5,gpt-5-mini,gpt-5-nano --input-token-count=1000 --output-token-count=500

# With caching scenarios
./vendor/bin/deus-token-calculator --compare-models=gpt-5,gpt-5-mini,gpt-5-nano --input-token-count=10000 --output-token-count=5000 --cache-hit-rate=0.25
```

#### Enterprise Model Comparison
```bash
# Large scale comparison
./vendor/bin/deus-token-calculator \
  --compare-models=gpt-5,gpt-5-mini,gpt-5-nano,gpt-4.1,gpt-4.1-mini \
  --input-token-count=5000 \
  --output-token-count=2000 \
  --cache-hit-rate=0.2 \
  --conversation-rounds=100 \
  --user-count=500
```

### Output Format Examples

#### Plain Text Output (Default)
```bash
# Standard text output
./vendor/bin/deus-token-calculator --model=gpt-5-mini --input-token-count=1000 --output-token-count=500

# Text output in Chinese
./vendor/bin/deus-token-calculator --model=gpt-5-mini --input-token-count=1000 --output-token-count=500 --language=zh-TW
```

#### JSON Output
```bash
# JSON format
./vendor/bin/deus-token-calculator --model=gpt-5-mini --input-token-count=1000 --output-token-count=500 --output-format=json

# JSON with model comparison
./vendor/bin/deus-token-calculator --compare-models=gpt-5,gpt-5-mini --input-token-count=1000 --output-token-count=500 --output-format=json

# Legacy JSON flag (still supported)
./vendor/bin/deus-token-calculator --model=gpt-5-mini --input-token-count=1000 --output-token-count=500 --export-json
```

#### Markdown Output
```bash
# Markdown format
./vendor/bin/deus-token-calculator --model=gpt-5-mini --input-token-count=1000 --output-token-count=500 --output-format=markdown

# Markdown comparison table
./vendor/bin/deus-token-calculator --compare-models=gpt-5,gpt-5-mini,gpt-5-nano --input-token-count=1000 --output-token-count=500 --output-format=markdown
```

### File Output Examples

#### Save to Files
```bash
# Save JSON to file
./vendor/bin/deus-token-calculator --model=gpt-5-mini --input-token-count=1000 --output-token-count=500 --output-format=json --output-file=results.json

# Save markdown comparison to file
./vendor/bin/deus-token-calculator --compare-models=gpt-5,gpt-5-mini,gpt-5-nano --input-token-count=1000 --output-token-count=500 --output-format=markdown --output-file=comparison.md

# Save text output to file
./vendor/bin/deus-token-calculator --model=gpt-5 --input-token-count=5000 --output-token-count=3000 --cache-hit-rate=0.3 --output-file=enterprise_cost.txt
```

### Interactive Mode Examples

#### Interactive CLI Session
```bash
# Launch interactive mode
./vendor/bin/deus-token-calculator --interactive-mode

# Interactive mode with language preference
./vendor/bin/deus-token-calculator --interactive-mode --language=zh-TW
```

### Custom Pricing Table Examples

#### Using Custom Pricing Data
```bash
# List models from custom pricing table
./vendor/bin/deus-token-calculator --list-available-models --pricing-table-path=/path/to/custom_pricing.csv

# Calculate with custom pricing
./vendor/bin/deus-token-calculator --model=custom-model --input-token-count=1000 --output-token-count=500 --pricing-table-path=/path/to/custom_pricing.csv

# Compare custom models
./vendor/bin/deus-token-calculator --compare-models=custom-model-1,custom-model-2 --input-token-count=1000 --output-token-count=500 --pricing-table-path=/path/to/custom_pricing.csv

# Show custom pricing data
./vendor/bin/deus-token-calculator --show-pricing-data --pricing-table-path=/path/to/custom_pricing.csv
```

### Real-World Scenario Examples

#### Chatbot Cost Estimation (High Cache Hit Rate)
```bash
# Chatbot with system prompts (70% cache hit rate)
./vendor/bin/deus-token-calculator \
  --model=gpt-5-mini \
  --input-token-count=8000 \
  --output-token-count=2000 \
  --cache-hit-rate=0.7 \
  --conversation-rounds=50 \
  --user-count=200 \
  --output-format=json \
  --output-file=chatbot_costs.json
```

#### API Service Cost Planning
```bash
# API service cost analysis
./vendor/bin/deus-token-calculator \
  --model=gpt-5 \
  --input-token-count=3000 \
  --output-token-count=1500 \
  --cache-hit-rate=0.15 \
  --conversation-rounds=1000 \
  --user-count=50 \
  --output-format=markdown \
  --output-file=api_service_costs.md
```

#### Content Generation Service
```bash
# Content generation with minimal caching
./vendor/bin/deus-token-calculator \
  --model=gpt-5-nano \
  --input-token-count=2000 \
  --output-token-count=4000 \
  --cache-hit-rate=0.05 \
  --conversation-rounds=200 \
  --user-count=100 \
  --language=zh-TW \
  --output-format=json
```

#### Cost Optimization Analysis
```bash
# Compare all models for cost optimization
./vendor/bin/deus-token-calculator \
  --compare-models=gpt-5,gpt-5-mini,gpt-5-nano,gpt-4.1,gpt-4.1-mini \
  --input-token-count=10000 \
  --output-token-count=5000 \
  --cache-hit-rate=0.3 \
  --conversation-rounds=100 \
  --user-count=1000 \
  --output-format=markdown \
  --output-file=cost_optimization.md \
  --language=en-US
```

### Advanced Parameter Combinations

#### All Parameters Example
```bash
# Using all available parameters
php cli/deus_llm_token_calculator.php \
  --model=gpt-5 \
  --input-token-count=15000 \
  --output-token-count=7500 \
  --cache-hit-rate=0.4 \
  --conversation-rounds=25 \
  --user-count=200 \
  --pricing-table-path=/path/to/custom_pricing.csv \
  --language=zh-TW \
  --output-format=json \
  --output-file=comprehensive_analysis.json
```

#### Batch Analysis Scripts
```bash
# Create multiple reports
php cli/deus_llm_token_calculator.php --compare-models=gpt-5,gpt-5-mini --input-token-count=1000 --output-token-count=500 --output-format=json --output-file=basic_comparison.json
php cli/deus_llm_token_calculator.php --compare-models=gpt-5,gpt-5-mini --input-token-count=1000 --output-token-count=500 --cache-hit-rate=0.5 --output-format=json --output-file=cached_comparison.json
php cli/deus_llm_token_calculator.php --compare-models=gpt-5,gpt-5-mini --input-token-count=1000 --output-token-count=500 --user-count=100 --output-format=json --output-file=scaled_comparison.json
```

### Language-Specific Examples

#### Traditional Chinese Interface
```bash
# All output in Traditional Chinese
php cli/deus_llm_token_calculator.php --model=gpt-5-mini --input-token-count=1000 --output-token-count=500 --language=zh-TW

# Chinese interactive mode
php cli/deus_llm_token_calculator.php --interactive-mode --language=zh-TW

# Chinese model comparison
php cli/deus_llm_token_calculator.php --compare-models=gpt-5,gpt-5-mini --input-token-count=1000 --output-token-count=500 --language=zh-TW --output-format=markdown
```

#### English Interface (Default)
```bash
# Explicit English (same as default)
php cli/deus_llm_token_calculator.php --model=gpt-5-mini --input-token-count=1000 --output-token-count=500 --language=en-US

# English help
php cli/deus_llm_token_calculator.php --help --language=en-US
```

## Testing

The package includes comprehensive test examples that you can run after installation:

```bash
# Run usage examples 
composer examples

# Test model extension functionality
php test_extension.php
```

## Key Features Explained

### Cache-Aware Pricing
The system understands that cached tokens (prompt cache hits) are typically priced at 90% savings compared to regular input tokens. Cache hit rate only applies to input tokens, not output tokens.

### Multi-User Scaling
Perfect for enterprise scenarios where you need to calculate costs across:
- Multiple users (user_count)
- Multiple conversations per user (conversation_rounds)
- Different models and configurations

### Flexible Output Formats
- **Text**: Human-readable console output
- **JSON**: Structured data for APIs and integrations
- **Markdown**: Documentation-friendly tables

### Multi-Language Support
Full internationalization with comprehensive translations for English and Traditional Chinese interfaces.

## Requirements

- PHP 7.2.5 or higher
- ext-json (required)
- ext-curl (suggested for HTTP-based pricing data fetching) 
- ext-mbstring (suggested for multi-byte string operations)

## Architecture

The Token Fee Guru follows modern PHP practices and design patterns:

### Core Components

- **Client**: Main entry point implementing `ClientInterface`
- **Calculator**: Token cost calculation logic implementing `CalculatorInterface`
- **DataSource**: Pluggable data sources implementing `DataSourceInterface`
- **RequestOptions**: Value object for calculation parameters
- **Exceptions**: Custom exception hierarchy for error handling

### Design Principles

- **Interface-based design**: All major components implement interfaces for flexibility
- **Dependency injection**: Components are injected rather than hardcoded
- **Single responsibility**: Each class has a focused purpose
- **Fluent interface**: Method chaining for easy configuration
- **Type safety**: Strict typing where possible
- **PSR compliance**: Follows PSR-4 autoloading and other relevant PSRs

### Extension Points

The library is designed to be extensible:

```php
// Custom data source
class ApiDataSource implements DataSourceInterface {
    // Implementation...
}

// Custom calculator
class AdvancedCalculator implements CalculatorInterface {
    // Implementation...
}

// Usage
$client = new Client([
    'data_source' => new ApiDataSource(),
    'calculator' => new AdvancedCalculator(),
]);
```

## License

This project follows the Deus AI development standards and conventions.

---

For detailed API documentation, see: `Deus_AI_Token_Fee_Guru/v1/Admin/README.md`