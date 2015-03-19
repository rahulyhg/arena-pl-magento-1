<?php

class ArenaPl_Magento_Block_Account extends Mage_Core_Block_Template
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
    * @return string
    */
   public function getSubdomain()
   {
       return (string) $this->accountModel->getSubdomain();
   }

   /**
    * @return string
    */
   public function getToken()
   {
       return (string) $this->accountModel->getToken();
   }
}
