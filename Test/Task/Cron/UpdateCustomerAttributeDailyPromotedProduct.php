<?php

namespace Test\Task\Cron;

use Exception;
use Magento\Customer\Model\Customer;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Profiler;
use Psr\Log\LoggerInterface;
use Test\Task\Model\Customer\CustomerIterator;

/**
 * Class UpdateCustomerAttributeDailyPromotedProduct
 * @package Test\Task\Cron
 */
class UpdateCustomerAttributeDailyPromotedProduct
{
    /**
     * @var ResourceConnection
     */
    private $resource;
    /**
     * @var CustomerIterator
     */
    private $customerIterator;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ResourceConnection $resource
     * @param CustomerIterator $customerIterator
     * @param AttributeRepositoryInterface $attributeRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $resource,
        CustomerIterator $customerIterator,
        AttributeRepositoryInterface $attributeRepository,
        LoggerInterface $logger
    ) {
        $this->resource = $resource;
        $this->customerIterator = $customerIterator;
        $this->attributeRepository = $attributeRepository;
        $this->logger = $logger;
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        $this->customerIterator->setMaxBatchSize(10);
        try {
            $attribute = $this->attributeRepository->get(Customer::ENTITY, 'daily_promoted_product');
            $attributeId = $attribute->getAttributeId();
            $productList = $this->getProductList();
            foreach ($this->customerIterator as $customer) {
                $this->setCustomerCustomAttribute($productList, $customer->getId(), $attributeId);
            }
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
    }

    /**
     * @return array
     */
    private function getProductList(): array
    {
        $result = [];
        Profiler::start('getProductListByAttributeStatusAndVisibility');
        try {
            $connection = $this->resource->getConnection();
            $select = $connection->select()
                ->from(['cpe' => 'catalog_product_entity'], 'cpe.entity_id')
                ->joinInner(['cpie' => 'catalog_product_index_eav'], 'cpe.entity_id = cpie.entity_id')
                ->joinInner(['ea' => 'eav_attribute'], 'cpie.attribute_id = ea.attribute_id and ea.attribute_code = "visibility"', [])
                ->distinct(true);
            $result = $connection->fetchAll($select);
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
        Profiler::stop('getProductListByAttributeStatusAndVisibility');
        return $result;
    }

    /**
     * @param $productList
     * @param $customerId
     * @param $attributeId
     * @return void
     */
    private function setCustomerCustomAttribute($productList, $customerId, $attributeId): void
    {
        Profiler::start('getProductListByAttributeStatusAndVisibility');
        try {
            $connection = $this->resource->getConnection();
            $connection->insertOnDuplicate('customer_entity_int', ['attribute_id' => $attributeId, 'entity_id' => $customerId], ['value' => array_rand($productList)]);
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
        Profiler::stop('getProductListByAttributeStatusAndVisibility');
    }
}
