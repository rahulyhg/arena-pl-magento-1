<?php

use ArenaPl\Client;

class ArenaPl_Magento_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @var Client
     */
    protected $client;

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
}
