<?php

/* @var $installer ArenaPl_Magento_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->addAttribute(
    Mage_Catalog_Model_Product::ENTITY,
    ArenaPl_Magento_Model_Exportservice::ATTRIBUTE_PRODUCT_VARIANT_ARENA_ID,
    [
        'type' => 'int',
        'label' => 'Arena.pl product variant id',
        'input' => 'text',
        'visible' => true,
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'required' => false,
        'user_defined' => true,
        'default' => null,
        'unique' => false,
        'group' => 'Arena.pl Settings',
        'note' => 'Podaj wyłącznie wartość liczbową id wariantu produktu',
        'frontend_class' => 'validate-digits',
]);

$installer->endSetup();
