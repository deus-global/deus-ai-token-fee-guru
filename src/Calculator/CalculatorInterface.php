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

namespace DeusGlobal\TokenFeeGuru\Calculator;

use DeusGlobal\TokenFeeGuru\RequestOptions;
use DeusGlobal\TokenFeeGuru\DataSource\DataSourceInterface;

/**
 * Interface for token cost calculators.
 */
interface CalculatorInterface
{
    /**
     * Calculate token costs.
     *
     * @param RequestOptions      $options
     * @param DataSourceInterface $dataSource
     * @return array
     */
    public function calculate(RequestOptions $options, DataSourceInterface $dataSource): array;

    /**
     * Compare costs across multiple models.
     *
     * @param array               $modelNames
     * @param RequestOptions      $options
     * @param DataSourceInterface $dataSource
     * @return array
     */
    public function compareModels(array $modelNames, RequestOptions $options, DataSourceInterface $dataSource): array;
}