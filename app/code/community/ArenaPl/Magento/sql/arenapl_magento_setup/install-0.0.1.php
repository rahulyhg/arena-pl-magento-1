<?php

/* @var $installer ArenaPl_Magento_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->addAttribute(
    Mage_Catalog_Model_Category::ENTITY,
    'arena_taxonomy_id',
    [
        'type' => 'int',
        'label' => 'Mapped taxonomy id',
        'input' => 'text',
        'visible' => true,
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'required' => false,
        'user_defined' => true,
        'default' => null,
        'unique' => false,
        'group' => 'Arena.pl Settings',
        'note' => 'Podaj wyłącznie wartość liczbową id kategorii',
        'frontend_class' => 'validate-digits',
]);

$installer->addAttribute(
    Mage_Catalog_Model_Category::ENTITY,
    'arena_taxon_id',
    [
        'type' => 'int',
        'label' => 'Mapped taxon id',
        'input' => 'text',
        'visible' => true,
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'required' => false,
        'user_defined' => true,
        'default' => null,
        'unique' => false,
        'group' => 'Arena.pl Settings',
        'note' => 'Podaj wyłącznie wartość liczbową id taksonu',
        'frontend_class' => 'validate-digits',
]);

$installer->addAttribute(
    Mage_Catalog_Model_Product::ENTITY,
    ArenaPl_Magento_Model_Exportservice::ATTRIBUTE_PRODUCT_ARENA_ID,
    [
        'type' => 'int',
        'label' => 'Arena.pl product id',
        'input' => 'text',
        'visible' => true,
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'required' => false,
        'user_defined' => true,
        'default' => null,
        'unique' => false,
        'group' => 'Arena.pl Settings',
        'note' => 'Podaj wyłącznie wartość liczbową id produktu',
        'frontend_class' => 'validate-digits',
]);

$installer->endSetup();
