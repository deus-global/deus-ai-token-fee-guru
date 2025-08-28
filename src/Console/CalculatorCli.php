<?php

namespace DeusGlobal\TokenFeeGuru\Console;

use DeusGlobal\TokenFeeGuru\Client;

class CalculatorCli
{
    private $calculator;
    private $options;
    private $language;
    private $translations;
    private $outputFormat = 'text';
    private $outputFile = null;

    public function __construct()
    {
        $this->parseArguments();
        
        // Get custom pricing table path if provided
        $pricingTablePath = $this->getOptionValue(['pricing-table-path'], null);
        $config = [];
        if ($pricingTablePath !== null) {
            $config['pricing_table_path'] = $pricingTablePath;
        }
        $this->calculator = new Client($config);
        
        $this->setLanguage($this->getOptionValue(['language', 'lang', 'l'], 'en-US'));
        $this->setOutputFormat();
        $this->loadTranslations();
    }

    public function run()
    {
        if (isset($this->options['help']) || isset($this->options['h'])) {
            $this->showHelp();
            return;
        }

        if (isset($this->options['list-available-models']) || isset($this->options['list-models'])) {
            $this->listAvailableModels();
            return;
        }

        if (isset($this->options['show-pricing-data']) || isset($this->options['pricing-data'])) {
            $this->showPricingData();
            return;
        }

        if (isset($this->options['compare-models']) || isset($this->options['compare'])) {
            $this->runModelComparison();
            return;
        }

        if (isset($this->options['interactive-mode']) || isset($this->options['interactive'])) {
            $this->runInteractiveMode();
            return;
        }

        $this->runSingleCalculation();
    }

    private function parseArguments()
    {
        // Parse both short and long options, including --param=value format
        $this->options = getopt(
            "h",
            [
                "help",
                "model:",
                "input-token-count:",
                "output-token-count:",
                "cache-hit-rate:",
                "conversation-rounds:",
                "user-count:",
                "pricing-table-path:",
                "language:",
                "list-available-models",
                "show-pricing-data",
                "compare-models:",
                "interactive-mode",
                "output-format:",
                "output-file:",
                "export-json"
            ]
        );
        
        // Also parse custom format for better semantic support
        $this->parseCustomArguments();
    }
    
    private function parseCustomArguments()
    {
        global $argv;
        
        for ($i = 1; $i < count($argv); $i++) {
            $arg = $argv[$i];
            
            // Handle --param=value format
            if (strpos($arg, '=') !== false && strpos($arg, '--') === 0) {
                list($param, $value) = explode('=', $arg, 2);
                $param = ltrim($param, '-');
                $this->options[$param] = $value;
            }
        }
    }

    private function setLanguage($lang)
    {
        $supportedLanguages = ['en-US', 'zh-TW'];
        $this->language = in_array($lang, $supportedLanguages) ? $lang : 'en-US';
    }
    
    private function setOutputFormat()
    {
        // Check for export-json flag first (backward compatibility)
        if (isset($this->options['export-json'])) {
            $this->outputFormat = 'json';
            return;
        }
        
        // Check for explicit output-format parameter
        $format = $this->getOptionValue(['output-format'], 'text');
        $supportedFormats = ['text', 'json', 'markdown'];
        $this->outputFormat = in_array($format, $supportedFormats) ? $format : 'text';
        
        // Set output file if specified
        $this->outputFile = $this->getOptionValue(['output-file'], null);
    }
    
    private function isJsonOutput()
    {
        return $this->outputFormat === 'json';
    }
    
    private function isMarkdownOutput()
    {
        return $this->outputFormat === 'markdown';
    }
    
    private function isTextOutput()
    {
        return $this->outputFormat === 'text';
    }
    
    private function output($content)
    {
        if ($this->outputFile) {
            file_put_contents($this->outputFile, $content);
            if ($this->isTextOutput()) {
                echo $this->t('output_saved') . " " . $this->outputFile . "\n";
            }
        } else {
            echo $content;
        }
    }

    private function loadTranslations()
    {
        $this->translations = [
            'en-US' => [
                'title' => 'LLM Token Fee Calculator CLI',
                'usage' => 'Usage: deus-token-calculator [OPTIONS]',
                'options' => 'OPTIONS:',
                'help_desc' => 'Show this help message',
                'model_desc' => 'Model name (e.g., gpt-5-mini)',
                'input_tokens_desc' => 'Input token count per conversation',
                'output_tokens_desc' => 'Output token count per conversation',
                'cache_rate_desc' => 'Cache hit rate (0.0 to 1.0, default: 0.0)',
                'rounds_desc' => 'Conversation rounds per user (default: 1)',
                'users_desc' => 'Number of users (default: 1)',
                'pricing_table_desc' => 'Custom pricing table CSV path',
                'lang_desc' => 'Output language (en-US, zh-TW, default: en-US)',
                'list_models_desc' => 'Show available models',
                'pricing_data_desc' => 'Show detailed pricing information',
                'compare_desc' => 'Compare multiple models (comma-separated)',
                'interactive_desc' => 'Run in interactive mode',
                'examples' => 'EXAMPLES:',
                'basic_calc' => '# Basic calculation',
                'enterprise_scenario' => '# Enterprise scenario',
                'compare_models' => '# Compare models',
                'interactive_mode' => '# Interactive mode',
                'list_models' => '# List available models',
                'chinese_output' => '# Chinese output',
                'available_models' => 'Available Models:',
                'pricing_info' => 'Model Pricing Information:',
                'cache_savings' => 'Cache Savings:',
                'comparison_results' => 'Model Comparison Results:',
                'parameters' => 'Parameters:',
                'cost_analysis' => 'Cost Analysis:',
                'most_economical' => 'Most Economical:',
                'most_expensive' => 'Most Expensive:',
                'potential_savings' => 'Potential Savings:',
                'configuration' => 'Configuration:',
                'token_breakdown' => 'Token Breakdown:',
                'cost_breakdown' => 'Cost Breakdown:',
                'regular_input_tokens' => 'Regular Input Tokens:',
                'cached_input_tokens' => 'Cached Input Tokens:',
                'regular_input_cost' => 'Regular Input Cost:',
                'cached_input_cost' => 'Cached Input Cost:',
                'output_cost' => 'Output Cost:',
                'cost_per_conversation' => 'Cost per Conversation:',
                'total_conversations' => 'Total for All Conversations:',
                'total_cost' => 'TOTAL COST FOR ALL USERS:',
                'interactive_title' => 'LLM Token Fee Calculator - Interactive Mode',
                'commands' => 'Available commands:',
                'single_calc' => 'Single calculation',
                'compare_models_cmd' => 'Compare models',
                'list_models_cmd' => 'List models',
                'pricing_data_cmd' => 'Show pricing data',
                'exit' => 'Exit',
                'select_option' => 'Select option (1-5): ',
                'goodbye' => 'Goodbye!',
                'invalid_option' => 'Invalid option. Please try again.',
                'enter_model' => 'Enter model name: ',
                'enter_input_tokens' => 'Enter input tokens per conversation: ',
                'enter_output_tokens' => 'Enter output tokens per conversation: ',
                'enter_cache_rate' => 'Enter cache hit rate (0.0-1.0, default 0.0): ',
                'enter_rounds' => 'Enter conversation rounds per user (default 1): ',
                'enter_users' => 'Enter number of users (default 1): ',
                'enter_models_compare' => 'Enter models to compare (comma-separated): ',
                'result' => 'Result:',
                'total_cost_label' => 'Total Cost:',
                'cost_per_conv_label' => 'Cost per Conversation:',
                'error' => 'Error:',
                'missing_params' => 'Missing required parameters',
                'required_params' => 'Required: --model, --input-token-count, --output-token-count',
                'run_help' => 'Run with --help for usage information',
                'no_models_specified' => 'No models specified for comparison',
                'usage_compare' => 'Usage: --compare-models=gpt-5,gpt-5-mini,gpt-5-nano',
                'output_saved' => 'Output saved to',
                'json_export_desc' => 'Export output as JSON format',
                'output_format_desc' => 'Output format (text, json, markdown, default: text)',
                'output_file_desc' => 'Save output to file'
            ],
            'zh-TW' => [
                'title' => 'LLM 語言模型代幣費用計算器 CLI',
                'usage' => '使用方式：deus-token-calculator [選項]',
                'options' => '選項：',
                'help_desc' => '顯示此說明訊息',
                'model_desc' => '模型名稱（例如：gpt-5-mini）',
                'input_tokens_desc' => '每次對話的輸入代幣數量',
                'output_tokens_desc' => '每次對話的輸出代幣數量',
                'cache_rate_desc' => '快取命中率（0.0 到 1.0，預設：0.0）',
                'rounds_desc' => '每位使用者的對話輪數（預設：1）',
                'users_desc' => '使用者數量（預設：1）',
                'pricing_table_desc' => '自訂價格表 CSV 路徑',
                'lang_desc' => '輸出語言（en-US, zh-TW，預設：en-US）',
                'list_models_desc' => '顯示可用模型',
                'pricing_data_desc' => '顯示詳細價格資訊',
                'compare_desc' => '比較多個模型（用逗號分隔）',
                'interactive_desc' => '以互動模式執行',
                'examples' => '範例：',
                'basic_calc' => '# 基本計算',
                'enterprise_scenario' => '# 企業場景',
                'compare_models' => '# 比較模型',
                'interactive_mode' => '# 互動模式',
                'list_models' => '# 列出可用模型',
                'chinese_output' => '# 中文輸出',
                'available_models' => '可用模型：',
                'pricing_info' => '模型價格資訊：',
                'cache_savings' => '快取節省：',
                'comparison_results' => '模型比較結果：',
                'parameters' => '參數：',
                'cost_analysis' => '成本分析：',
                'most_economical' => '最經濟：',
                'most_expensive' => '最昂貴：',
                'potential_savings' => '潛在節省：',
                'configuration' => '設定：',
                'token_breakdown' => '代幣明細：',
                'cost_breakdown' => '成本明細：',
                'regular_input_tokens' => '一般輸入代幣：',
                'cached_input_tokens' => '快取輸入代幣：',
                'regular_input_cost' => '一般輸入成本：',
                'cached_input_cost' => '快取輸入成本：',
                'output_cost' => '輸出成本：',
                'cost_per_conversation' => '每次對話成本：',
                'total_conversations' => '所有對話總成本：',
                'total_cost' => '所有使用者總成本：',
                'interactive_title' => 'LLM 語言模型代幣費用計算器 - 互動模式',
                'commands' => '可用指令：',
                'single_calc' => '單一計算',
                'compare_models_cmd' => '比較模型',
                'list_models_cmd' => '列出模型',
                'pricing_data_cmd' => '顯示價格資料',
                'exit' => '退出',
                'select_option' => '選擇選項（1-5）：',
                'goodbye' => '再見！',
                'invalid_option' => '無效選項，請重新嘗試。',
                'enter_model' => '輸入模型名稱：',
                'enter_input_tokens' => '輸入每次對話的輸入代幣數：',
                'enter_output_tokens' => '輸入每次對話的輸出代幣數：',
                'enter_cache_rate' => '輸入快取命中率（0.0-1.0，預設 0.0）：',
                'enter_rounds' => '輸入每位使用者的對話輪數（預設 1）：',
                'enter_users' => '輸入使用者數量（預設 1）：',
                'enter_models_compare' => '輸入要比較的模型（用逗號分隔）：',
                'result' => '結果：',
                'total_cost_label' => '總成本：',
                'cost_per_conv_label' => '每次對話成本：',
                'error' => '錯誤：',
                'missing_params' => '缺少必要參數',
                'required_params' => '必要參數：--model、--input-token-count、--output-token-count',
                'run_help' => '執行 --help 查看使用方式資訊',
                'no_models_specified' => '未指定要比較的模型',
                'usage_compare' => '使用方式：--compare-models=gpt-5,gpt-5-mini,gpt-5-nano',
                'output_saved' => '輸出已儲存至',
                'json_export_desc' => '以 JSON 格式匯出輸出',
                'output_format_desc' => '輸出格式（text、json、markdown，預設：text）',
                'output_file_desc' => '儲存輸出至檔案'
            ]
        ];
    }

    private function t($key)
    {
        return $this->translations[$this->language][$key] ?? $key;
    }

    private function showHelp()
    {
        echo $this->t('title') . "\n";
        echo str_repeat('=', mb_strlen($this->t('title'), 'UTF-8')) . "\n\n";
        echo $this->t('usage') . "\n\n";
        echo $this->t('options') . "\n";
        echo "  -h, --help                              " . $this->t('help_desc') . "\n";
        echo "      --model=MODEL                       " . $this->t('model_desc') . "\n";
        echo "      --input-token-count=NUM             " . $this->t('input_tokens_desc') . "\n";
        echo "      --output-token-count=NUM            " . $this->t('output_tokens_desc') . "\n";
        echo "      --cache-hit-rate=RATE               " . $this->t('cache_rate_desc') . "\n";
        echo "      --conversation-rounds=NUM           " . $this->t('rounds_desc') . "\n";
        echo "      --user-count=NUM                    " . $this->t('users_desc') . "\n";
        echo "      --pricing-table-path=PATH           " . $this->t('pricing_table_desc') . "\n";
        echo "      --language=LANG                     " . $this->t('lang_desc') . "\n";
        echo "      --output-format=FORMAT              " . $this->t('output_format_desc') . "\n";
        echo "      --output-file=FILE                  " . $this->t('output_file_desc') . "\n";
        echo "      --export-json                       " . $this->t('json_export_desc') . "\n";
        echo "      --list-available-models             " . $this->t('list_models_desc') . "\n";
        echo "      --show-pricing-data                 " . $this->t('pricing_data_desc') . "\n";
        echo "      --compare-models=MODELS             " . $this->t('compare_desc') . "\n";
        echo "      --interactive-mode                  " . $this->t('interactive_desc') . "\n\n";
        echo $this->t('examples') . "\n";
        echo "  " . $this->t('basic_calc') . "\n";
        echo "  deus-token-calculator --model=gpt-5-mini --input-token-count=2000 --output-token-count=1000\n\n";
        echo "  " . $this->t('enterprise_scenario') . "\n";
        echo "  deus-token-calculator --model=gpt-5 --input-token-count=5000 --output-token-count=3000 --cache-hit-rate=0.3 --conversation-rounds=20 --user-count=500\n\n";
        echo "  " . $this->t('compare_models') . "\n";
        echo "  deus-token-calculator --compare-models=gpt-5,gpt-5-mini,gpt-5-nano --input-token-count=1000 --output-token-count=500\n\n";
        echo "  " . $this->t('interactive_mode') . "\n";
        echo "  deus-token-calculator --interactive-mode\n\n";
        echo "  " . $this->t('list_models') . "\n";
        echo "  deus-token-calculator --list-available-models\n\n";
        echo "  " . $this->t('chinese_output') . "\n";
        echo "  deus-token-calculator --language=zh-TW --model=gpt-5-mini --input-token-count=1000 --output-token-count=500\n\n";
    }

    private function listAvailableModels()
    {
        $models = $this->calculator->getAvailableModels();
        
        if ($this->isJsonOutput()) {
            $output = json_encode([
                'action' => 'list_available_models',
                'timestamp' => date('c'),
                'data' => [
                    'available_models' => $models,
                    'total_count' => count($models)
                ]
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $this->output($output . "\n");
        } elseif ($this->isMarkdownOutput()) {
            $content = "# " . $this->t('available_models') . "\n\n";
            foreach ($models as $model) {
                $content .= "- `$model`\n";
            }
            $content .= "\n**Total Models:** " . count($models) . "\n\n";
            $this->output($content);
        } else {
            $content = $this->t('available_models') . "\n";
            $content .= str_repeat('=', mb_strlen($this->t('available_models'), 'UTF-8')) . "\n";
            foreach ($models as $model) {
                $content .= "  • $model\n";
            }
            $content .= "\n";
            $this->output($content);
        }
    }

    private function showPricingData()
    {
        $pricingData = $this->calculator->getPricingData();
        
        if ($this->isJsonOutput()) {
            $processedData = [];
            foreach ($pricingData as $modelName => $pricing) {
                $savings = round(((1 - ($pricing['cached_input_token_price'] / $pricing['input_token_price'])) * 100), 1);
                $processedData[$modelName] = [
                    'vendor' => $pricing['vendor'],
                    'model' => $pricing['model'],
                    'model_api_name' => $pricing['model_api_name'],
                    'pricing' => [
                        'input_token_price_per_million' => $pricing['input_token_price'],
                        'cached_input_token_price_per_million' => $pricing['cached_input_token_price'],
                        'output_token_price_per_million' => $pricing['output_token_price'],
                        'cache_savings_percentage' => $savings
                    ],
                    'raw_pricing_strings' => [
                        'input_token_price' => $pricing['raw_input_token_price_string'],
                        'cached_input_token_price' => $pricing['raw_cached_input_token_price_string'],
                        'output_token_price' => $pricing['raw_output_token_price_string']
                    ]
                ];
            }
            
            $output = json_encode([
                'action' => 'show_pricing_data',
                'timestamp' => date('c'),
                'data' => [
                    'models' => $processedData,
                    'total_models' => count($processedData)
                ]
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $this->output($output . "\n");
        } elseif ($this->isMarkdownOutput()) {
            $content = "# " . $this->t('pricing_info') . "\n\n";
            $content .= "| Model | Vendor | Input Price | Cached Input Price | Output Price | Cache Savings |\n";
            $content .= "|-------|--------|-------------|-------------------|--------------|---------------|\n";
            
            foreach ($pricingData as $modelName => $pricing) {
                $savings = round(((1 - ($pricing['cached_input_token_price'] / $pricing['input_token_price'])) * 100), 1);
                $content .= "| `$modelName` | {$pricing['vendor']} | \${$pricing['input_token_price']}/1M | \${$pricing['cached_input_token_price']}/1M | \${$pricing['output_token_price']}/1M | {$savings}% |\n";
            }
            $content .= "\n**Total Models:** " . count($pricingData) . "\n\n";
            $this->output($content);
        } else {
            $content = $this->t('pricing_info') . "\n";
            $content .= str_repeat('=', mb_strlen($this->t('pricing_info'), 'UTF-8')) . "\n";
            
            foreach ($pricingData as $modelName => $pricing) {
                $currency = isset($pricing['currency']) ? $pricing['currency'] : 'USD';
                $content .= "\n$modelName ({$pricing['vendor']} {$pricing['model']}):\n";
                $content .= "  Input Tokens:  \${$pricing['input_token_price']} {$currency} / 1M tokens\n";
                $content .= "  Cached Input:  \${$pricing['cached_input_token_price']} {$currency} / 1M tokens\n";
                $content .= "  Output Tokens: \${$pricing['output_token_price']} {$currency} / 1M tokens\n";
                $savings = round(((1 - ($pricing['cached_input_token_price'] / $pricing['input_token_price'])) * 100), 1);
                $content .= "  " . $this->t('cache_savings') . " {$savings}%\n";
            }
            $content .= "\n";
            $this->output($content);
        }
    }

    private function runModelComparison()
    {
        $modelsString = $this->getOptionValue(['compare-models', 'compare'], '');
        if (empty($modelsString)) {
            echo $this->t('error') . " " . $this->t('no_models_specified') . "\n";
            echo $this->t('usage_compare') . "\n";
            return;
        }

        $models = array_map('trim', explode(',', $modelsString));
        
        $inputTokens = $this->getOptionValue(['input-token-count', 'input-tokens', 'i'], 1000);
        $outputTokens = $this->getOptionValue(['output-token-count', 'output-tokens', 'o'], 500);
        $cacheRate = $this->getOptionValue(['cache-hit-rate', 'cache-rate', 'c'], 0.0);
        $rounds = $this->getOptionValue(['conversation-rounds', 'rounds', 'r'], 1);
        $users = $this->getOptionValue(['user-count', 'users', 'u'], 1);

        try {
            $comparison = $this->calculator
                ->setInputTokens($inputTokens)
                ->setOutputTokens($outputTokens)
                ->setCacheHitRate($cacheRate)
                ->setConversationRounds($rounds)
                ->setUserCount($users)
                ->compareModels($models);

            if ($this->isJsonOutput()) {
                $output = json_encode([
                    'action' => 'compare_models',
                    'timestamp' => date('c'),
                    'input_parameters' => [
                        'models' => $models,
                        'input_token_count' => $inputTokens,
                        'output_token_count' => $outputTokens,
                        'cache_hit_rate' => $cacheRate,
                        'conversation_rounds' => $rounds,
                        'user_count' => $users
                    ],
                    'comparison_result' => $comparison
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                $this->output($output . "\n");
            } else {
                $content = $this->t('comparison_results') . "\n";
                $content .= str_repeat('=', mb_strlen($this->t('comparison_results'), 'UTF-8')) . "\n";
                $content .= $this->t('parameters') . "\n";
                $content .= "  Input Tokens: " . number_format($inputTokens) . "\n";
                $content .= "  Output Tokens: " . number_format($outputTokens) . "\n";
                $content .= "  Cache Hit Rate: " . ($cacheRate * 100) . "%\n";
                $content .= "  Rounds per User: " . number_format($rounds) . "\n";
                $content .= "  Total Users: " . number_format($users) . "\n\n";
                
                foreach ($comparison['model_comparison_results'] as $modelName => $result) {
                    if (isset($result['cost_breakdown'])) {
                        $totalCost = $result['cost_breakdown']['total_cost_for_all_users'];
                        $costPerConversation = $result['cost_breakdown']['cost_per_conversation'];
                        
                        $content .= "$modelName:\n";
                        $content .= "  Cost per Conversation: \$" . number_format($costPerConversation, 6) . "\n";
                        $content .= "  Total Cost: \$" . number_format($totalCost, 2) . "\n\n";
                    } else {
                        $content .= "$modelName: ERROR - " . ($result['error'] ?? 'Unknown error') . "\n\n";
                    }
                }
                
                $this->output($content);
            }

        } catch (\Exception $e) {
            if ($this->isJsonOutput()) {
                $output = json_encode([
                    'error' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode()
                    ]
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                $this->output($output . "\n");
            } else {
                $this->output($this->t('error') . " " . $e->getMessage() . "\n");
            }
        }
    }

    private function runSingleCalculation()
    {
        $model = $this->getOptionValue(['model', 'm']);
        $inputTokens = $this->getOptionValue(['input-token-count', 'input-tokens', 'i']);
        $outputTokens = $this->getOptionValue(['output-token-count', 'output-tokens', 'o']);

        if (!$model || !$inputTokens || !$outputTokens) {
            echo $this->t('error') . " " . $this->t('missing_params') . "\n";
            echo $this->t('required_params') . "\n";
            echo $this->t('run_help') . "\n";
            return;
        }

        $cacheRate = $this->getOptionValue(['cache-hit-rate', 'cache-rate', 'c'], 0.0);
        $rounds = $this->getOptionValue(['conversation-rounds', 'rounds', 'r'], 1);
        $users = $this->getOptionValue(['user-count', 'users', 'u'], 1);

        try {
            $result = $this->calculator
                ->setModel($model)
                ->setInputTokens($inputTokens)
                ->setOutputTokens($outputTokens)
                ->setCacheHitRate($cacheRate)
                ->setConversationRounds($rounds)
                ->setUserCount($users)
                ->calculate();

            if ($this->isJsonOutput()) {
                $output = json_encode([
                    'action' => 'single_model_calculation',
                    'timestamp' => date('c'),
                    'input_parameters' => [
                        'model' => $model,
                        'input_token_count' => $inputTokens,
                        'output_token_count' => $outputTokens,
                        'cache_hit_rate' => $cacheRate,
                        'conversation_rounds' => $rounds,
                        'user_count' => $users
                    ],
                    'calculation_result' => $result
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                $this->output($output . "\n");
            } else {
                $content = "LLM Token Cost Calculation:\n";
                $content .= "===========================\n\n";
                
                $content .= $this->t('configuration') . "\n";
                $content .= "  Model: {$result['model_name']}\n";
                $content .= "  Input Tokens: " . number_format($result['input_token_count']) . "\n";
                $content .= "  Output Tokens: " . number_format($result['output_token_count']) . "\n";
                $content .= "  Cache Hit Rate: " . ($result['cache_hit_rate'] * 100) . "%\n";
                $content .= "  Conversation Rounds: " . number_format($result['conversation_round_count']) . "\n";
                $content .= "  Users: " . number_format($result['user_count']) . "\n\n";
                
                $content .= $this->t('token_breakdown') . "\n";
                $content .= "  " . $this->t('regular_input_tokens') . " " . number_format($result['token_breakdown']['regular_input_tokens']) . "\n";
                $content .= "  " . $this->t('cached_input_tokens') . " " . number_format($result['token_breakdown']['cached_input_tokens']) . "\n\n";
                
                $content .= $this->t('cost_breakdown') . "\n";
                $costBreakdown = $result['cost_breakdown'];
                $content .= "  " . $this->t('regular_input_cost') . " \$" . number_format($costBreakdown['regular_input_cost'], 6) . "\n";
                $content .= "  " . $this->t('cached_input_cost') . " \$" . number_format($costBreakdown['cached_input_cost'], 6) . "\n";
                $content .= "  " . $this->t('output_cost') . " \$" . number_format($costBreakdown['output_cost'], 6) . "\n";
                $content .= "  ─────────────────────────────────────\n";
                $content .= "  " . $this->t('cost_per_conversation') . " \$" . number_format($costBreakdown['cost_per_conversation'], 6) . "\n";
                $content .= "  " . $this->t('total_conversations') . " \$" . number_format($costBreakdown['total_cost_for_all_conversations'], 2) . "\n";
                $content .= "  " . $this->t('total_cost') . " \$" . number_format($costBreakdown['total_cost_for_all_users'], 2) . "\n\n";
                
                $this->output($content);
            }

        } catch (\Exception $e) {
            if ($this->isJsonOutput()) {
                $output = json_encode([
                    'error' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode()
                    ]
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                $this->output($output . "\n");
            } else {
                $this->output($this->t('error') . " " . $e->getMessage() . "\n");
            }
        }
    }

    private function runInteractiveMode()
    {
        echo $this->t('interactive_title') . "\n";
        echo str_repeat('=', mb_strlen($this->t('interactive_title'), 'UTF-8')) . "\n\n";

        while (true) {
            echo $this->t('commands') . "\n";
            echo "  1) " . $this->t('single_calc') . "\n";
            echo "  2) " . $this->t('compare_models_cmd') . "\n";
            echo "  3) " . $this->t('list_models_cmd') . "\n";
            echo "  4) " . $this->t('pricing_data_cmd') . "\n";
            echo "  5) " . $this->t('exit') . "\n\n";

            $choice = $this->promptInput($this->t('select_option'));

            switch ($choice) {
                case '1':
                    $this->interactiveSingleCalculation();
                    break;
                case '2':
                    $this->interactiveModelComparison();
                    break;
                case '3':
                    $this->listAvailableModels();
                    break;
                case '4':
                    $this->showPricingData();
                    break;
                case '5':
                    echo $this->t('goodbye') . "\n";
                    return;
                default:
                    echo $this->t('invalid_option') . "\n\n";
            }

            echo "─────────────────────────────────────────────────\n\n";
        }
    }

    private function interactiveSingleCalculation()
    {
        echo "\n" . $this->t('single_calc') . ":\n";
        echo str_repeat('=', mb_strlen($this->t('single_calc'), 'UTF-8') + 1) . "\n";

        $this->listAvailableModels();
        $model = $this->promptInput($this->t('enter_model'));
        $inputTokens = (int)$this->promptInput($this->t('enter_input_tokens'));
        $outputTokens = (int)$this->promptInput($this->t('enter_output_tokens'));
        $cacheRate = (float)$this->promptInput($this->t('enter_cache_rate'), "0.0");
        $rounds = (int)$this->promptInput($this->t('enter_rounds'), "1");
        $users = (int)$this->promptInput($this->t('enter_users'), "1");

        try {
            $result = $this->calculator
                ->reset()
                ->setModel($model)
                ->setInputTokens($inputTokens)
                ->setOutputTokens($outputTokens)
                ->setCacheHitRate($cacheRate)
                ->setConversationRounds($rounds)
                ->setUserCount($users)
                ->calculate();

            echo "\n" . $this->t('result') . "\n";
            echo $this->t('total_cost_label') . " \$" . number_format($result['cost_breakdown']['total_cost_for_all_users'], 2) . "\n";
            echo $this->t('cost_per_conv_label') . " \$" . number_format($result['cost_breakdown']['cost_per_conversation'], 6) . "\n\n";

        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n\n";
        }
    }

    private function interactiveModelComparison()
    {
        echo "\n" . $this->t('compare_models_cmd') . ":\n";
        echo str_repeat('=', mb_strlen($this->t('compare_models_cmd'), 'UTF-8') + 1) . "\n";

        $this->listAvailableModels();
        $modelsInput = $this->promptInput($this->t('enter_models_compare'));
        $models = array_map('trim', explode(',', $modelsInput));
        
        $inputTokens = (int)$this->promptInput($this->t('enter_input_tokens'));
        $outputTokens = (int)$this->promptInput($this->t('enter_output_tokens'));
        $cacheRate = (float)$this->promptInput($this->t('enter_cache_rate'), "0.0");
        $rounds = (int)$this->promptInput($this->t('enter_rounds'), "1");
        $users = (int)$this->promptInput($this->t('enter_users'), "1");

        try {
            $comparison = $this->calculator
                ->reset()
                ->setInputTokens($inputTokens)
                ->setOutputTokens($outputTokens)
                ->setCacheHitRate($cacheRate)
                ->setConversationRounds($rounds)
                ->setUserCount($users)
                ->compareModels($models);

            echo "\nComparison Results:\n";
            foreach ($comparison['model_comparison_results'] as $modelName => $result) {
                if (isset($result['cost_breakdown'])) {
                    echo "$modelName: \$" . number_format($result['cost_breakdown']['total_cost_for_all_users'], 2) . "\n";
                }
            }
            echo "\n";

        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n\n";
        }
    }

    private function promptInput($prompt, $default = null)
    {
        echo $prompt;
        $input = trim(fgets(STDIN));
        return empty($input) && $default !== null ? $default : $input;
    }

    private function getOptionValue($keys, $default = null)
    {
        if (!is_array($keys)) {
            $keys = [$keys];
        }
        
        foreach ($keys as $key) {
            if (isset($this->options[$key])) {
                return $this->options[$key];
            }
        }
        
        return $default;
    }
}