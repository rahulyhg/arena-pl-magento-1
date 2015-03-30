<?php

use ArenaPl\Client;

class ArenaPl_Magento_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CACHE_DEFAULT_TAG = 'arenapl';

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Varien_Cache_Core
     */
    protected $cache;

    public function __construct()
    {
        $this->cache = Mage::app()->getCache();
    }

    /**
     * @return Varien_Db_Adapter_Interface
     */
    public static function getDBReadConnection()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_read');
    }

    /**
     * @return Varien_Db_Adapter_Interface
     */
    public static function getDBWriteConnection()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    /**
     * @param int $categoryId
     *
     * @return Mage_Catalog_Model_Category|null
     */
    public static function getCategory($categoryId)
    {
        $categoryId = (int) $categoryId;
        if (empty($categoryId)) {
            return;
        }

        /* @var $collection Mage_Catalog_Model_Resource_Category_Collection */
        $collection = Mage::getModel('catalog/category')->getCollection();
        $collection->addAttributeToFilter('entity_id', ['eq' => $categoryId]);
        $collection->addAttributeToSelect('*');

        $collectionArray = iterator_to_array($collection);
        if (empty($collectionArray)) {
            return;
        }

        return current($collectionArray);
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        if (!$this->client) {
            $this->client = $this->createClient();
        }

        return $this->client;
    }

    /**
     * @return Client
     */
    protected function createClient()
    {
        /* @var $accountModel ArenaPl_Magento_Model_Account */
        $accountModel = Mage::getModel('arenapl_magento/account');

        return new Client(
            $accountModel->getSubdomain(),
            $accountModel->getToken(),
            Mage::getIsDeveloperMode()
        );
    }

    /**
     * @param string   $key
     * @param callable $saveFunction function call result to cache
     * @param array    $tags
     * @param int      $timeout      in secs
     *
     * @return array
     */
    public function cacheExpensiveCall($key, callable $saveFunction, array $tags, $timeout)
    {
        $cacheValue = $this->cache->load($key);
        if ($cacheValue !== false) {
            return unserialize($cacheValue);
        }

        $funcValue = $saveFunction();

        if (empty($funcValue)) {
            return $funcValue;
        }

        $tags[] = self::CACHE_DEFAULT_TAG;
        $this->cache->save(
            serialize($funcValue),
            $key,
            array_unique($tags),
            $timeout
        );

        return $funcValue;
    }
}
