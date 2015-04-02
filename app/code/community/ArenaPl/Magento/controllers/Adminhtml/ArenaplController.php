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

    public function synchronizationAction()
    {
        $this->loadLayout();

        $this->renderLayout();
    }

    public function productFullResyncAction()
    {
        /* @var $session Mage_Core_Model_Session */
        $session = Mage::getSingleton('core/session');

        try {
            /* @var $exporter ArenaPl_Magento_Model_ExportService */
            $exporter = Mage::getSingleton('arenapl_magento/exportservice');

            $syncErrors = $exporter->fullProductResync();
            if ($syncErrors) {
                $session->addWarning(sprintf(
                    'Błędy synchronizacji produktów: %d', count($syncErrors)
                ));

                $parsedSyncErrors = [];
                foreach ($syncErrors as $productEntityId => $errorData) {
                    $parsedSyncErrors[$productEntityId] = [
                        'product_name' => $errorData['product']->getName(),
                        'error_message' => $errorData['exception']->getMessage(),
                    ];
                }

                $session->setData(
                    ArenaPl_Magento_Block_Synchronization::PRODUCT_SYNC_ERRORS_PARAM_KEY,
                    $parsedSyncErrors
                );
            } else {
                $session->addSuccess('Poprawnie wykonano synchronizację produktów');
            }
        } catch (\Exception $e) {
            $session->addError($e->getMessage());
        }

        $this->_redirect('*/*/synchronization');
    }

    public function saveCategoryAttributesAction()
    {
        $data = $this->getRequest()->getPost();

        $categoryId = empty($data['category_id']) ? 0 : (int) $data['category_id'];
        $attributesMapping = empty($data['attributes_mapping']) ? [] : $data['attributes_mapping'];
        $optionsMapping = empty($data['options_mapping']) ? [] : $data['options_mapping'];

        /* @var $mapper ArenaPl_Magento_Model_Mapper */
        $mapper = Mage::getSingleton('arenapl_magento/mapper');

        if ($mapper->saveCategoryAttributes($categoryId, $attributesMapping, $optionsMapping)) {
            echo $this->returnOkAjax();
        } else {
            echo $this->returnErroredAjax('Błąd zapisu atrybutów kategorii');
        }
    }

    public function saveCategoryMappingAction()
    {
        $data = $this->getRequest()->getPost();

        if (empty($data['arenapl_taxons'])) {
            echo $this->returnOkAjax();

            return;
        }

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
            echo $this->returnOkAjax();
        } else {
            echo $this->returnErroredAjax('Błąd zapisu kategorii');
        }
    }

    public function categoryAttributesAction()
    {
        $this->loadLayout();

        $this->renderLayout();
    }

    public function ajaxAction()
    {
        $request = $this->getRequest();

        $task = $request->get('task');
        switch ($task) {
            case 'load_taxon_tree':
                $selectedTaxonomy = (string) $request->get('selected_taxonomy');
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
            case 'load_taxon_attributes_num':
                $selectedTaxonomy = (string) $request->get('selected_taxonomy');
                if (empty($selectedTaxonomy)) {
                    echo $this->returnErroredAjax('empty selected taxonomy');
                }

                list($categoryEntityId, $taxonomyId, $taxonId) = explode('-', $selectedTaxonomy, 3);

                /* @var $mapper ArenaPl_Magento_Model_Mapper */
                $mapper = Mage::getSingleton('arenapl_magento/mapper');

                $attrubitesNum = (int) $mapper->getTotalCategoryAttributesNum(
                    $taxonomyId,
                    $taxonId
                );

                echo $this->returnOkAjax([
                    'attributes_num' => $attrubitesNum,
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
