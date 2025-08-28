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

namespace DeusGlobal\TokenFeeGuru\Tests\Unit;

use DeusGlobal\TokenFeeGuru\Client;
use DeusGlobal\TokenFeeGuru\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    /** @var Client */
    private $client;

    protected function setUp(): void
    {
        $this->client = new Client([
            'pricing_table_path' => __DIR__ . '/../../data/ai_token_pricing_table.csv'
        ]);
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(Client::class, $this->client);
    }

    public function testSetModelReturnsSelf(): void
    {
        $result = $this->client->setModel('gpt-5-mini');
        $this->assertSame($this->client, $result);
    }

    public function testSetInputTokensReturnsSelf(): void
    {
        $result = $this->client->setInputTokens(1000);
        $this->assertSame($this->client, $result);
    }

    public function testSetOutputTokensReturnsSelf(): void
    {
        $result = $this->client->setOutputTokens(500);
        $this->assertSame($this->client, $result);
    }

    public function testSetCacheHitRateValidatesRange(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->client->setCacheHitRate(1.5);
    }

    public function testSetCacheHitRateValidatesNegative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->client->setCacheHitRate(-0.1);
    }

    public function testSetCacheHitRateAcceptsValidRange(): void
    {
        $result = $this->client->setCacheHitRate(0.5);
        $this->assertSame($this->client, $result);
    }

    public function testGetAvailableModels(): void
    {
        $models = $this->client->getAvailableModels();
        $this->assertIsArray($models);
        $this->assertNotEmpty($models);
        $this->assertContains('gpt-5-mini', $models);
    }

    public function testGetPricingData(): void
    {
        $pricingData = $this->client->getPricingData();
        $this->assertIsArray($pricingData);
        $this->assertNotEmpty($pricingData);
        $this->assertArrayHasKey('gpt-5-mini', $pricingData);
    }

    public function testBasicCalculation(): void
    {
        $result = $this->client
            ->setModel('gpt-5-mini')
            ->setInputTokens(1000)
            ->setOutputTokens(500)
            ->calculate();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('model_name', $result);
        $this->assertArrayHasKey('cost_breakdown', $result);
        $this->assertEquals('gpt-5-mini', $result['model_name']);
        $this->assertEquals(1000, $result['input_token_count']);
        $this->assertEquals(500, $result['output_token_count']);
    }

    public function testCalculationWithCache(): void
    {
        $result = $this->client
            ->setModel('gpt-5-mini')
            ->setInputTokens(1000)
            ->setOutputTokens(500)
            ->setCacheHitRate(0.3)
            ->calculate();

        $this->assertEquals(0.3, $result['cache_hit_rate']);
        $this->assertArrayHasKey('token_breakdown', $result);
        $this->assertEquals(300, $result['token_breakdown']['cached_input_tokens']);
        $this->assertEquals(700, $result['token_breakdown']['regular_input_tokens']);
    }

    public function testCompareModels(): void
    {
        $result = $this->client
            ->setInputTokens(1000)
            ->setOutputTokens(500)
            ->compareModels(['gpt-5-mini', 'gpt-5-nano']);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('comparison_parameters', $result);
        $this->assertArrayHasKey('model_comparison_results', $result);
        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('gpt-5-mini', $result['model_comparison_results']);
        $this->assertArrayHasKey('gpt-5-nano', $result['model_comparison_results']);
    }

    public function testResetClearsOptions(): void
    {
        $this->client
            ->setModel('gpt-5-mini')
            ->setInputTokens(1000)
            ->setOutputTokens(500)
            ->reset();

        // After reset, calculation should fail due to missing model
        $this->expectException(InvalidArgumentException::class);
        $this->client->calculate();
    }
}