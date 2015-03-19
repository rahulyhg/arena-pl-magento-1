<?php

class ArenaPl_Magento_Block_Configchecker extends Mage_Core_Block_Template
{
    /**
     * @var ArenaPl_Magento_Model_Account
     */
    protected $accountModel;

    public function _construct()
    {
        parent::_construct();

        $this->accountModel = Mage::getModel('arenapl_magento/account');
    }

    /**
     * @return bool
     */
    public function shouldDisplaySettingsNotSetWarning()
    {
        return !$this->accountModel->isClientConfigured();
    }

    /**
     * @return bool
     */
    public function shouldDisplayWrongSettings()
    {
        /* @var $helper ArenaPl_Magento_Helper_Data */
        $helper = Mage::helper('arenapl_magento');
        $client = $helper->getClient();

        try {
            $getTaxonomies = $client->getTaxonomies();
            $getTaxonomies
                ->setPage(1)
                ->setResultsPerPage(1)
                ->getResult();

            return false;
        } catch (\Exception $e) {
            return true;
        }
    }
}
