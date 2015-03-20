<?php

interface ArenaPl_Magento_EventInterface
{
    /**
     * Event data:
     * - 'taxons_data' => array.
     */
    const EVENT_PRE_SAVE_MAPPED_CATEGORIES = 'arenapl_pre_save_mapped_categories';

    /**
     * Event data:
     * - 'saved_categories' => Mage_Catalog_Model_Resource_Category_Collection.
     */
    const EVENT_POST_SAVE_MAPPED_CATEGORIES = 'arenapl_post_save_mapped_categories';
}
