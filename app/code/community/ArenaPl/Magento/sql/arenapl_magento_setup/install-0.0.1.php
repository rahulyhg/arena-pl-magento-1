<?php

/* @var $installer ArenaPl_Magento_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->addAttribute(
    Mage_Catalog_Model_Category::ENTITY,
    ArenaPl_Magento_Model_Mapper::ATTRIBUTE_CATALOG_ARENA_TAXONOMY_ID,
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
    ArenaPl_Magento_Model_Mapper::ATTRIBUTE_CATALOG_ARENA_TAXON_ID,
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

$installer->endSetup();
