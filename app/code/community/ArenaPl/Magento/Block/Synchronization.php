<?php

class ArenaPl_Magento_Block_Synchronization extends Mage_Core_Block_Template
{
    /**
     * Session key.
     */
    const PRODUCT_SYNC_ERRORS_PARAM_KEY = 'arenapl_product_sync_errors';

    /**
     * @var array
     */
    protected $productSyncErrors = [];

    public function _construct()
    {
        parent::_construct();

        /* @var $session Mage_Core_Model_Session */
        $session = Mage::getSingleton('core/session');

        $productSyncErrorsParam = $session->getData(
            self::PRODUCT_SYNC_ERRORS_PARAM_KEY,
            true
        );

        if (!empty($productSyncErrorsParam)) {
            $this->productSyncErrors = $productSyncErrorsParam;
        }
    }

    /**
     * @return bool
     */
    public function hasProductSyncErrors()
    {
        return !empty($this->productSyncErrors);
    }

    /**
     * @return array
     */
    public function getProductSyncErrors()
    {
        return $this->productSyncErrors;
    }
}
