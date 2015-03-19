<?php

class ArenaPl_Magento_Model_Account extends Mage_Core_Model_Abstract
{
    /**
     * Configuration path to settings.
     */
    const XML_PATH_SETTINGS = 'arenapl/settings';

    /**
     * @var string
     */
    protected $subdomain;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var bool
     */
    protected $dataFetched = false;

    /**
     * @return string
     */
    public function getSubdomain()
    {
        $this->ensureAccountDataFetched();

        return $this->subdomain;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        $this->ensureAccountDataFetched();

        return $this->token;
    }

    /**
     * @return bool
     */
    public function isClientConfigured()
    {
        return !empty($this->getSubdomain()) && !empty($this->getToken());
    }

    /**
     * Assigns subdomain and token data from store config.
     */
    protected function ensureAccountDataFetched()
    {
        if (!$this->dataFetched) {
            $settings = Mage::getStoreConfig(self::XML_PATH_SETTINGS);

            $this->subdomain = empty($settings['subdomain']) ? '' : (string) $settings['subdomain'];
            $this->token = empty($settings['token']) ? '' : (string) $settings['token'];

            $this->dataFetched = true;
        }
    }
}
