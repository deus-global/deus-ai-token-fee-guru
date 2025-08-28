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

namespace DeusGlobal\TokenFeeGuru\Tests\Unit\Calculator;

use DeusGlobal\TokenFeeGuru\Calculator\TokenCalculator;
use DeusGlobal\TokenFeeGuru\RequestOptions;
use DeusGlobal\TokenFeeGuru\DataSource\DataSourceInterface;
use DeusGlobal\TokenFeeGuru\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TokenCalculatorTest extends TestCase
{
    /** @var TokenCalculator */
    private $calculator;

    /** @var DataSourceInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $dataSource;

    protected function setUp(): void
    {
        $this->calculator = new TokenCalculator();
        $this->dataSource = $this->createMock(DataSourceInterface::class);
    }

    public function testCalculateWithValidData(): void
    {
        $options = new RequestOptions();
        $options->setModel('test-model');
        $options->setInputTokens(1000);
        $options->setOutputTokens(500);
        $options->setCacheHitRate(0.2);

        $pricingData = [
            'vendor' => 'Test Vendor',
            'model' => 'Test Model',
            'model_api_name' => 'test-model',
            'input_token_price' => 1.0,
            'cached_input_token_price' => 0.1,
            'output_token_price' => 2.0,
            'currency' => 'USD',
        ];

        $this->dataSource
            ->expects($this->once())
            ->method('getModelPricing')
            ->with('test-model')
            ->willReturn($pricingData);

        $result = $this->calculator->calculate($options, $this->dataSource);

        $this->assertIsArray($result);
        $this->assertEquals('test-model', $result['model_name']);
        $this->assertEquals(1000, $result['input_token_count']);
        $this->assertEquals(500, $result['output_token_count']);
        $this->assertEquals(0.2, $result['cache_hit_rate']);

        // Check token breakdown
        $this->assertEquals(200, $result['token_breakdown']['cached_input_tokens']);
        $this->assertEquals(800, $result['token_breakdown']['regular_input_tokens']);

        // Check cost calculations
        $expectedRegularInputCost = (800 * 1.0) / 1000000; // 0.0008
        $expectedCachedInputCost = (200 * 0.1) / 1000000; // 0.00002
        $expectedOutputCost = (500 * 2.0) / 1000000; // 0.001

        $this->assertEquals(round($expectedRegularInputCost, 6), $result['cost_breakdown']['regular_input_cost']);
        $this->assertEquals(round($expectedCachedInputCost, 6), $result['cost_breakdown']['cached_input_cost']);
        $this->assertEquals(round($expectedOutputCost, 6), $result['cost_breakdown']['output_cost']);
    }

    public function testCalculateThrowsExceptionWhenModelMissing(): void
    {
        $options = new RequestOptions();
        // Not setting model name

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Model name is required');

        $this->calculator->calculate($options, $this->dataSource);
    }

    public function testCalculateThrowsExceptionWhenModelNotFound(): void
    {
        $options = new RequestOptions();
        $options->setModel('nonexistent-model');

        $this->dataSource
            ->expects($this->once())
            ->method('getModelPricing')
            ->with('nonexistent-model')
            ->willThrowException(new \Exception('Model not found'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Model "nonexistent-model" not found in pricing data');

        $this->calculator->calculate($options, $this->dataSource);
    }

    public function testCompareModelsWithValidData(): void
    {
        $options = new RequestOptions();
        $options->setInputTokens(1000);
        $options->setOutputTokens(500);

        $modelData = [
            'test-model-1' => [
                'input_token_price' => 1.0,
                'cached_input_token_price' => 0.1,
                'output_token_price' => 2.0,
            ],
            'test-model-2' => [
                'input_token_price' => 2.0,
                'cached_input_token_price' => 0.2,
                'output_token_price' => 4.0,
            ],
        ];

        $this->dataSource
            ->method('getModelPricing')
            ->willReturnCallback(function ($modelName) use ($modelData) {
                if (isset($modelData[$modelName])) {
                    return array_merge([
                        'vendor' => 'Test Vendor',
                        'model' => $modelName,
                        'model_api_name' => $modelName,
                        'currency' => 'USD',
                    ], $modelData[$modelName]);
                }
                throw new \Exception('Model not found');
            });

        $result = $this->calculator->compareModels(['test-model-1', 'test-model-2'], $options, $this->dataSource);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('comparison_parameters', $result);
        $this->assertArrayHasKey('model_comparison_results', $result);
        $this->assertArrayHasKey('summary', $result);

        // Check that both models were calculated
        $this->assertArrayHasKey('test-model-1', $result['model_comparison_results']);
        $this->assertArrayHasKey('test-model-2', $result['model_comparison_results']);

        // Check summary
        $this->assertEquals('success', $result['summary']['status']);
        $this->assertEquals(2, $result['summary']['total_models_compared']);
        $this->assertEquals(2, $result['summary']['successful_calculations']);
    }

    public function testCompareModelsThrowsExceptionWhenEmpty(): void
    {
        $options = new RequestOptions();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Model names array cannot be empty');

        $this->calculator->compareModels([], $options, $this->dataSource);
    }
}