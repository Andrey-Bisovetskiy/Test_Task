<?php
declare(strict_types=1);

namespace Test\Task\Model\Customer;

use Iterator;
use Psr\Log\InvalidArgumentException;

/**
 * Class CustomerIterator
 */
class CustomerIterator implements Iterator
{
    /**
     * @var CustomerFetcher
     */
    private $fetcher;

    /**
     * @var int
     */
    private $maxBatchSize = 10;

    /**
     * @var mixed|null
     */
    private $current;

    /**
     * @var mixed[]
     */
    private $currentBatch = [];

    /**
     * @var int
     */
    private $key;

    /**
     * CustomerIterator constructor.
     *
     * @param CustomerFetcher $fetcher
     */
    public function __construct(
        CustomerFetcher $fetcher
    ) {
        $this->fetcher = $fetcher;
    }

    /**
     * @param int $maxBatchSize
     *
     * @throws InvalidArgumentException
     */
    public function setMaxBatchSize(int $maxBatchSize): void
    {
        if ($maxBatchSize < 1) {
            throw new InvalidArgumentException('Max batch size should be an int bigger than 0.');
        }

        $this->maxBatchSize = $maxBatchSize;
    }

    /**
     * Returns an item of the type returned by the BatchingFetcher,
     * or null if there are no further values.
     *
     * @return mixed
     */
    public function current()
    {
        return $this->current;
    }

    /**
     * {@inheritDoc}
     * Get the next item.
     * If no items then will be getting a new batch of entities.
     */
    public function next(): void
    {
        $value = array_shift($this->currentBatch);

        if ($value === null) {
            $this->nextItemFromNewBatch();
        } else {
            $this->current = $value;
            $this->key++;
        }
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return $this->current !== null;
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->fetcher->rewind();
        $this->key = -1;
        $this->next();
    }

    /**
     * Get the next item from new batch
     */
    private function nextItemFromNewBatch(): void
    {
        $this->currentBatch = $this->fetcher->fetchNext($this->maxBatchSize);

        if (empty($this->currentBatch)) {
            $this->current = null;
        } else {
            $this->next();
        }
    }
}
