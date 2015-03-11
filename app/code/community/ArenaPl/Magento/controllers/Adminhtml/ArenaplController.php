<?php

class ArenaPl_Magento_Adminhtml_ArenaplController extends Mage_Adminhtml_Controller_Action
{
    public function ordersAction()
    {
        $this->setActiveArenaMenu();

        $this->setActiveBlock('arenapl_orders', 'arenapl/orders.phtml');

        $this->renderLayout();
    }

    public function accountAction()
    {
        $this->setActiveArenaMenu();

        $this->setActiveBlock('arenapl_account', 'arenapl/account.phtml');

        $this->renderLayout();
    }

    public function categoriesAction()
    {
        $this->setActiveArenaMenu();

        $this->setActiveBlock('arenapl_categories', 'arenapl/categories.phtml');

        $this->renderLayout();
    }

    public function helpAction()
    {
        $this->setActiveArenaMenu();

        $this->setActiveBlock('arenapl_help', 'arenapl/help.phtml');

        $this->renderLayout();
    }

    protected function setActiveArenaMenu()
    {
        $layout = $this->loadLayout();
        $layout->_setActiveMenu('arenapl_magento');
    }

    protected function setActiveBlock($blockName, $templateFile)
    {
        $block = $this->getLayout()->createBlock(
            'Mage_Core_Block_Template',
            $blockName,
            [
                'template' => $templateFile,
            ]
        );

        $this->getLayout()->getBlock('content')->append($block);
    }
}
