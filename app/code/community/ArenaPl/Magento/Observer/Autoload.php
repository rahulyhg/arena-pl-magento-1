<?php

class ArenaPl_Magento_Observer_Autoload
{
    /**
     * @var bool
     */
    protected $autoloaderInjected = false;

    /**
     * @param Varien_Event_Observer $observer
     */
    public function injectComposerAutoloader(Varien_Event_Observer $observer)
    {
        if (!$this->autoloaderInjected) {
            $autoloadInjector = new ArenaPl_Magento_AutoloadInjector();
            $this->autoloaderInjected = $autoloadInjector->injectComposerAutoloader();
        }
    }
}
