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

/**
 * Interface for token fee calculation clients.
 */
interface ClientInterface
{
    /**
     * Set the model name for calculations.
     *
     * @param string $modelName
     * @return ClientInterface
     */
    public function setModel(string $modelName): ClientInterface;

    /**
     * Set the number of input tokens.
     *
     * @param int $tokens
     * @return ClientInterface
     */
    public function setInputTokens(int $tokens): ClientInterface;

    /**
     * Set the number of output tokens.
     *
     * @param int $tokens
     * @return ClientInterface
     */
    public function setOutputTokens(int $tokens): ClientInterface;

    /**
     * Set the cache hit rate (0.0 to 1.0).
     *
     * @param float $rate
     * @return ClientInterface
     */
    public function setCacheHitRate(float $rate): ClientInterface;

    /**
     * Set the number of conversation rounds.
     *
     * @param int $rounds
     * @return ClientInterface
     */
    public function setConversationRounds(int $rounds): ClientInterface;

    /**
     * Set the number of users.
     *
     * @param int $users
     * @return ClientInterface
     */
    public function setUserCount(int $users): ClientInterface;

    /**
     * Calculate token costs with current options.
     *
     * @return array
     */
    public function calculate(): array;

    /**
     * Calculate costs for multiple models.
     *
     * @param array $modelNames
     * @return array
     */
    public function compareModels(array $modelNames): array;

    /**
     * Get available models.
     *
     * @return array
     */
    public function getAvailableModels(): array;

    /**
     * Get pricing data for all models.
     *
     * @return array
     */
    public function getPricingData(): array;

    /**
     * Reset all calculation options to defaults.
     *
     * @return ClientInterface
     */
    public function reset(): ClientInterface;
}