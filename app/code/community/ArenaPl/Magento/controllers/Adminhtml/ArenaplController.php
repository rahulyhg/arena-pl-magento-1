<?php

class ArenaPl_Magento_Adminhtml_ArenaplController extends Mage_Adminhtml_Controller_Action
{
    public function ordersAction()
    {
        $layout = $this->loadLayout();
        $layout->_setActiveMenu('arenapl_magento');

        $block = $this->getLayout()->createBlock(
                'Mage_Core_Block_Template', 'arenapl_orders', ['template' => 'arenapl/orders.phtml']
        );

        $this->getLayout()->getBlock('content')->append($block);

        $this->renderLayout();
    }
}
