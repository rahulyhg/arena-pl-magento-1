<?php

class ArenaPl_Magento_Observer_Product
{
    /**
     * @var int[]
     */
    protected $productsAlreadyHandled = [];

    /**
     * @param Varien_Event_Observer $observer
     */
    public function afterProductSave(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();

        /* @var $product Mage_Catalog_Model_Product */
        $product = $event->getProduct();

        $productEntityId = $product->getEntityId();
        if (in_array($productEntityId, $this->productsAlreadyHandled)) {
            return;
        }

        $this->productsAlreadyHandled[] = $productEntityId;

        $freshProduct = $product->load($productEntityId);

        try {
            /* @var $exporter ArenaPl_Magento_Model_ExportService */
            $exporter = Mage::getSingleton('arenapl_magento/exportservice');
            $exporter->exportProduct($freshProduct);
        } catch (\Exception $e) {
            Mage::logException($e);
        }
    }
    
    /**
     * @param Varien_Event_Observer $observer
     */
    public function onProductDuplicate(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        
        try {
            /* @var $exporter ArenaPl_Magento_Model_ExportService */
            $exporter = Mage::getSingleton('arenapl_magento/exportservice');
            $exporter->cleanArenaData($event->getNewProduct());
        } catch (\Exception $e) {
            Mage::logException($e);
        }
    }
}
