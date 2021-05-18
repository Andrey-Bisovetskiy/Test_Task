<?php
declare(strict_types=1);

namespace Test\Task\Model\Customer;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Psr\Log\LoggerInterface;
use Test\Task\Api\Data\BatchingFetcherInterface;

/**
 * Class CustomerFetcher
 * @package Test\Task\Model\Customer
 */
class CustomerFetcher implements BatchingFetcherInterface
{
    private $currentPage = 0;

    private $pageSize = 0;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        LoggerInterface $logger
    ) {
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     * Get next batch of customers
     */
    public function fetchNext(int $batchSize): array
    {
        $items = [];
        try {
            $this->pageSize += $batchSize;
            $searchCriteria = $this->searchCriteriaBuilder
                ->setCurrentPage($this->currentPage)
                ->setPageSize($batchSize)
                ->create();
            $customerList = $this->customerRepository->getList($searchCriteria)->getItems();
            if ($customerList) {
                $items = $customerList;
                $this->currentPage++;
            }
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
        return $items;
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->currentPage = 1;
        $this->pageSize = 0;
    }
}
