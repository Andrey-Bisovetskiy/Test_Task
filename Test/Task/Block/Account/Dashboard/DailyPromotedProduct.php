<?php

namespace Test\Task\Block\Account\Dashboard;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class DailyPromotedProduct
 * @package Test\Task\Block\Account\Dashboard
 */
class DailyPromotedProduct extends Template
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Template\Context $context
     * @param Session $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Session $customerSession,
        ProductRepositoryInterface $productRepository,
        LoggerInterface $logger,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
        $this->productRepository = $productRepository;
        $this->storeManager = $context->getStoreManager();
        $this->logger = $logger;
    }

    /**
     * @return ProductInterface|string
     */
    public function getProductByCustomerAttribute(): ?ProductInterface
    {
        $result = '';
        try {
            $customer = $this->customerSession->getCustomer();
            $productId = $customer->getDailyPromotedProduct();
            $result = $this->productRepository->getById($productId);
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
        return $result;
    }

    /**
     * @param $imagePath
     * @return string
     */
    public function getImageByPath($imagePath): string
    {
        $result = '';
        try {
            return $this->getUrlPath() . 'catalog/product' . $imagePath;
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getUrlPath(): string
    {
        $result = '';
        try {
            $store = $this->storeManager->getStore();
            $result = $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
        return $result;
    }
}
