<?php

class ArenaPl_Magento_Adminhtml_ArenaplController extends Mage_Adminhtml_Controller_Action
{
    public function ordersAction()
    {
        $this->loadLayout();

        $this->renderLayout();
    }

    public function accountAction()
    {
        $this->loadLayout();

        $this->renderLayout();
    }

    public function categoriesAction()
    {
        $this->loadLayout();

        $this->renderLayout();
    }

    public function helpAction()
    {
        $this->loadLayout();

        $this->renderLayout();
    }

    public function saveCategoryMappingAction()
    {
        $data = $this->getRequest()->getPost();

        $taxonsData = [];
        foreach ($data['arenapl_taxons'] as $selectValue) {
            $exploded = explode('-', $selectValue);
            $taxonsData[$exploded[0]] = [
                'taxonomy_id' => $exploded[1],
                'taxon_id' => $exploded[2],
            ];
        }

        /* @var $mapper ArenaPl_Magento_Model_Mapper */
        $mapper = Mage::getSingleton('arenapl_magento/mapper');
        if ($mapper->saveCategoryMappings($taxonsData)) {
            Mage::getSingleton('adminhtml/session')
                ->addSuccess('Poprawnie zapisano kategorie');

            echo $this->returnOkAjax();
        } else {
            echo $this->returnErroredAjax('Błąd zapisu kategorii');
        }
    }

    public function ajaxAction()
    {
        $request = $this->getRequest();

        $task = $request->get('task');
        switch ($task) {
            case 'load_taxon_tree':
                $selectedTaxonomy = $request->get('selected_taxonomy');
                if (empty($selectedTaxonomy)) {
                    echo $this->returnErroredAjax('empty selected taxonomy');
                }

                list($categoryEntityId, $taxonomyId, $taxonId) = explode('-', $selectedTaxonomy, 3);

                $this->loadLayout();

                /* @var $categoriesBlock ArenaPl_Magento_Block_Categories */
                $categoriesBlock = $this->getLayout()->createBlock('arenapl_magento/categories');
                $categoriesBlock->setTemplate('arenapl/ajax_load_taxon_tree.phtml');
                $categoriesBlock->addData([
                    'category_entity_id' => $categoryEntityId,
                    'taxonomy_id' => $taxonomyId,
                    'taxon_id' => $taxonId,
                ]);

                echo $this->returnOkAjax([
                    'html' => $categoriesBlock->toHtml(),
                ]);
                break;
            default:
                echo $this->returnErroredAjax('task unrecognized');
                break;
        }
    }

    /**
     * @param array $data
     *
     * @return string
     */
    protected function returnOkAjax(array $data = [])
    {
        return json_encode(array_merge([
            'status' => 'ok',
        ], $data));
    }

    /**
     * @param string $message
     *
     * @return string
     */
    protected function returnErroredAjax($message)
    {
        return json_encode([
            'status' => 'error',
            'message' => $message,
        ]);
    }
}