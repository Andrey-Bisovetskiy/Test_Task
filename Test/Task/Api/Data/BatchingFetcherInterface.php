<?php
declare(strict_types=1);

namespace Test\Task\Api\Data;

/**
 * Interface BatchingFetcherInterface
 * @package Cwb\Intact\Model\Base
 */
interface BatchingFetcherInterface
{
    /**
     * Returns up to $batchSize values.
     *
     * @param int $batchSize
     * @return array
     */
    public function fetchNext(int $batchSize): array;

    /**
     * Rewind the BatchingFetcher to the first element.
     */
    public function rewind(): void;
}
