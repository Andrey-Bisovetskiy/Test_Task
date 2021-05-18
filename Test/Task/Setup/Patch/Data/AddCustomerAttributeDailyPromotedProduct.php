<?php

namespace Test\Task\Setup\Patch\Data;

use Exception;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;

/**
 * Class AddCustomerAttributeDailyPromotedProduct
 * @package Test\Task\Setup\Patch\Data
 */
class AddCustomerAttributeDailyPromotedProduct implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var SetFactory
     */
    private $attributeSetFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        ModuleDataSetupInterface $moduleDataSetup,
        LoggerInterface $logger,
        SetFactory $attributeSetFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->logger = $logger;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        try {
            /** @var CustomerSetup $customerSetup */
            $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
            $customerEntity = $customerSetup->getEavConfig()->getEntityType(Customer::ENTITY);
            $attributeSetId = $customerEntity->getDefaultAttributeSetId();
            $attributeSet = $this->attributeSetFactory->create();
            $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
            $customerSetup->addAttribute(
                Customer::ENTITY,
                'daily_promoted_product',
                [
                    'label' => 'Daily Promoted Product',
                    'input' => 'text',
                    'type' => 'int',
                    'source' => '',
                    'required' => false,
                    'position' => 333,
                    'visible' => true,
                    'system' => false,
                    'is_used_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'is_searchable_in_grid' => false,
                    'backend' => ''
                ]
            );
            $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'daily_promoted_product');
            $attribute->setData([
                'used_in_forms' => [
                    'adminhtml_customer',
                    'customer_account_edit'
                ]
            ]);
            $attribute->setData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId
            ]);
            $attribute->save();

        } catch (Exception $exception) {
            $this->logger->critical($exception);
        }
        $this->moduleDataSetup->endSetup();
    }

    /**
     * {@inheritDoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public static function getDependencies()
    {
        return [];
    }
}
