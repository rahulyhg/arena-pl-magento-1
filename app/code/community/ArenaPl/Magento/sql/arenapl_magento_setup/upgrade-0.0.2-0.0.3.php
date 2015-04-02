<?php

/* @var $installer ArenaPl_Magento_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->removeAttribute(Mage_Catalog_Model_Category::ENTITY, 'arena_taxonomy_id');
$installer->removeAttribute(Mage_Catalog_Model_Category::ENTITY, 'arena_taxon_id');

$installer->addAttribute(
    Mage_Catalog_Model_Category::ENTITY,
    ArenaPl_Magento_Model_Mapper::ATTRIBUTE_CATEGORY_ARENA_TAXONOMY_PERMALINK,
    [
        'type' => 'varchar',
        'label' => 'Mapped taxonomy permalink',
        'input' => 'text',
        'visible' => true,
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'required' => false,
        'user_defined' => true,
        'default' => null,
        'unique' => false,
        'group' => 'Arena.pl Settings',
        'note' => 'Podaj permalink taksonomii',
]);

$installer->addAttribute(
    Mage_Catalog_Model_Category::ENTITY,
    ArenaPl_Magento_Model_Mapper::ATTRIBUTE_CATEGORY_ARENA_TAXON_PERMALINK,
    [
        'type' => 'varchar',
        'label' => 'Mapped taxon permalink',
        'input' => 'text',
        'visible' => true,
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'required' => false,
        'user_defined' => true,
        'default' => null,
        'unique' => false,
        'group' => 'Arena.pl Settings',
        'note' => 'Podaj permalink taksonu',
]);

$installer->endSetup();
