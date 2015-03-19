<?php

use ArenaPl\Client;

class ArenaPl_Magento_Helper_Data extends Mage_Core_Helper_Abstract
{
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
     * @param callable $saveFunction
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

        $tags[] = 'arenapl';
        $this->cache->save(
            serialize($funcValue),
            $key,
            array_unique($tags),
            $timeout
        );

        return $funcValue;
    }
}
