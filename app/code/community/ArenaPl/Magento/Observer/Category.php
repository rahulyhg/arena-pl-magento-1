<?php

class ArenaPl_Magento_Observer_Category
{
    /**
     * Configuration path to notifications settings.
     */
    const XML_PATH_NOTIFICATIONS = 'arenapl/notifications';

    /**
     * @var bool
     */
    protected $showUnmappedNotifications;

    /**
     * @param Varien_Event_Observer $observer
     */
    public function afterCategorySave(Varien_Event_Observer $observer)
    {
        if ($this->configAllowsUnmappedCategoryNotification()) {
            try {
                $event = $observer->getEvent();
                $this->addNotificationIfUnmappedCategory($event->getCategory());
            } catch (\Exception $e) {
                Mage::logException($e);
            }
        }
    }

    /**
     * @return bool
     */
    protected function configAllowsUnmappedCategoryNotification()
    {
        if ($this->showUnmappedNotifications === null) {
            $settings = Mage::getStoreConfig('arenapl/notifications');

            $this->showUnmappedNotifications = (bool) $settings['notify_unmapped_category'];
        }

        return $this->showUnmappedNotifications;
    }

    /**
     * @param Mage_Catalog_Model_Category $category
     */
    protected function addNotificationIfUnmappedCategory(Mage_Catalog_Model_Category $category)
    {
        /* @var $mapper ArenaPl_Magento_Model_Mapper */
        $mapper = Mage::getSingleton('arenapl_magento/mapper');

        if (!$mapper->hasMappedTaxon($category)) {
            $entityId = $category->getEntityId();
            $targetUrl = Mage::helper('adminhtml')
                ->getUrl('/arenapl/categories')
                . sprintf('#category-%d', $entityId);

            $category->load($entityId, 'name');

            /* @var $inbox Mage_AdminNotification_Model_Inbox */
            $inbox = Mage::getModel('adminnotification/inbox');
            $inbox->addNotice(
                '[Arena.pl] Niezamapowana kategoria',
                sprintf(
                    'Kategoria "%s" wymaga zamapowania, <a href="%s">zamapuj</a>',
                    $category->getName(),
                    $targetUrl
                ),
                $targetUrl
            );
        }
    }
}
