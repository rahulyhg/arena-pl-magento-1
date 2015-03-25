<?php

/* @var $installer ArenaPl_Magento_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
    CREATE TABLE IF NOT EXISTS `arenapl_mapper_attribute` (
      `attribute_id` smallint(5) unsigned NOT NULL,
      `arena_option_name` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
      PRIMARY KEY (`attribute_id`),
      CONSTRAINT `FK_ARENAPL_MAPPER_EAV_ATTRIBUTE` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
");

$installer->run("
    CREATE TABLE IF NOT EXISTS `arenapl_mapper_attribute_value` (
      `value_id` int(10) unsigned NOT NULL,
      `arena_option_value_name` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
      PRIMARY KEY (`value_id`),
      CONSTRAINT `FK_ARENAPL_MAPPER_EAV_ATTRIBUTE_VALUE` FOREIGN KEY (`value_id`) REFERENCES `eav_attribute_option_value` (`value_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
");

$installer->endSetup();