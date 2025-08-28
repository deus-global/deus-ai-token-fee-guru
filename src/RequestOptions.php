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
 * Request options for token calculations.
 */
class RequestOptions
{
    /** @var string|null */
    private $model;
    
    /** @var int */
    private $inputTokens = 0;
    
    /** @var int */
    private $outputTokens = 0;
    
    /** @var float */
    private $cacheHitRate = 0.0;
    
    /** @var int */
    private $conversationRounds = 1;
    
    /** @var int */
    private $userCount = 1;

    /**
     * Set the model name.
     *
     * @param string $model
     * @return void
     */
    public function setModel(string $model): void
    {
        $this->model = $model;
    }

    /**
     * Get the model name.
     *
     * @return string|null
     */
    public function getModel(): ?string
    {
        return $this->model;
    }

    /**
     * Set the number of input tokens.
     *
     * @param int $tokens
     * @return void
     */
    public function setInputTokens(int $tokens): void
    {
        $this->inputTokens = $tokens;
    }

    /**
     * Get the number of input tokens.
     *
     * @return int
     */
    public function getInputTokens(): int
    {
        return $this->inputTokens;
    }

    /**
     * Set the number of output tokens.
     *
     * @param int $tokens
     * @return void
     */
    public function setOutputTokens(int $tokens): void
    {
        $this->outputTokens = $tokens;
    }

    /**
     * Get the number of output tokens.
     *
     * @return int
     */
    public function getOutputTokens(): int
    {
        return $this->outputTokens;
    }

    /**
     * Set the cache hit rate.
     *
     * @param float $rate
     * @return void
     */
    public function setCacheHitRate(float $rate): void
    {
        $this->cacheHitRate = $rate;
    }

    /**
     * Get the cache hit rate.
     *
     * @return float
     */
    public function getCacheHitRate(): float
    {
        return $this->cacheHitRate;
    }

    /**
     * Set the number of conversation rounds.
     *
     * @param int $rounds
     * @return void
     */
    public function setConversationRounds(int $rounds): void
    {
        $this->conversationRounds = $rounds;
    }

    /**
     * Get the number of conversation rounds.
     *
     * @return int
     */
    public function getConversationRounds(): int
    {
        return $this->conversationRounds;
    }

    /**
     * Set the number of users.
     *
     * @param int $users
     * @return void
     */
    public function setUserCount(int $users): void
    {
        $this->userCount = $users;
    }

    /**
     * Get the number of users.
     *
     * @return int
     */
    public function getUserCount(): int
    {
        return $this->userCount;
    }

    /**
     * Convert to array representation.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'model' => $this->model,
            'input_tokens' => $this->inputTokens,
            'output_tokens' => $this->outputTokens,
            'cache_hit_rate' => $this->cacheHitRate,
            'conversation_rounds' => $this->conversationRounds,
            'user_count' => $this->userCount,
        ];
    }
}