<?php

interface ArenaPl_Magento_EventInterface
{
    /**
     * Event data:
     * - 'product' => Mage_Catalog_Model_Product.
     */
    const EVENT_PRE_PRODUCT_EXPORT = 'arenapl_pre_product_export';

    /**
     * Event data:
     * - 'product' => Mage_Catalog_Model_Product.
     */
    const EVENT_POST_PRODUCT_EXPORT = 'arenapl_post_product_export';

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

    /**
     * Event data:
     * - 'taxons_data' => array.
     */
    const EVENT_PRE_SAVE_MAPPED_CATEGORY_ATTRIBUTES = 'arenapl_pre_save_mapped_category_attrs';

    /**
     * Event data:
     * - 'saved_categories' => Mage_Catalog_Model_Resource_Category_Collection.
     */
    const EVENT_POST_SAVE_MAPPED_CATEGORY_ATTRIBUTES = 'arenapl_post_save_mapped_category_attrs';

    /**
     * Event data:
     * - 'products_collection' => Mage_Catalog_Model_Resource_Product_Collection.
     */
    const EVENT_PRE_PRODUCT_FULL_RESYNC = 'arenapl_pre_product_full_resync';

    /**
     * Event data:
     * - 'products_collection' => Mage_Catalog_Model_Resource_Product_Collection
     * - 'errors' => \Exception[].
     */
    const EVENT_POST_PRODUCT_FULL_RESYNC = 'arenapl_post_product_full_resync';
}
