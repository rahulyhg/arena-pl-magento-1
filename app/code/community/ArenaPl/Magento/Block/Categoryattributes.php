<?php

class ArenaPl_Magento_Block_Categoryattributes extends Mage_Core_Block_Template
{
    const ATTRIBUTE_TYPE_PROPERTY = 'property';
    const ATTRIBUTE_TYPE_OPTION_VALUE = 'option_value';

    /**
     * @var Mage_Catalog_Model_Category|null
     */
    protected $categoryToMap;

    /**
     * @var array|null
     */
    protected $mappedTaxonData;

    /**
     * @var ArenaPl_Magento_Model_Mapper
     */
    protected $mapper;

    public function _construct()
    {
        parent::_construct();

        $this->mapper = Mage::getSingleton('arenapl_magento/mapper');

        $this->initCategoryToMap();
        $this->initMappedTaxon();
    }

    protected function initCategoryToMap()
    {
        $this->categoryToMap = null;

        /* @var $request Mage_Core_Controller_Request_Http */
        $request = $this->getRequest();

        $categoryId =  (int) $request->getQuery('category_id', 0);

        $this->categoryToMap = ArenaPl_Magento_Helper_Data::getCategory($categoryId);
    }

    protected function initMappedTaxon()
    {
        $this->mappedTaxonData = null;

        if (!$this->isCategoryFound()) {
            return;
        }

        if (!$this->isCategoryMapped()) {
            return;
        }

        $this->mappedTaxonData = $this->mapper->getMappedArenaTaxon($this->categoryToMap);
    }

    /**
     * @return bool
     */
    public function isCategoryFound()
    {
        return $this->categoryToMap instanceof Mage_Catalog_Model_Category;
    }

    /**
     * @return bool
     *
     * @throws \RuntimeException When no category to map
     */
    public function isCategoryMapped()
    {
        if (!$this->isCategoryFound()) {
            throw new \RuntimeException('No category to map');
        }

        return $this->mapper->hasMappedTaxon($this->categoryToMap);
    }

    /**
     * @return bool
     */
    public function hasMappedTaxon()
    {
        return !empty($this->mappedTaxonData);
    }

    /**
     * @return Mage_Catalog_Model_Category|null
     */
    public function getMappedCategory()
    {
        return $this->categoryToMap;
    }

    /**
     * @return array|null
     */
    public function getMappedTaxon()
    {
        return $this->mappedTaxonData;
    }

    /**
     * @return array
     *
     * @throws \RuntimeException When no mapped taxon
     */
    public function getPrototypeAttributes()
    {
        if (!$this->hasMappedTaxon()) {
            throw new \RuntimeException('No mapped taxon');
        }

        $prototypeAttributes = $this->mapper->getCategoryAttributes(
            $this->mappedTaxonData['taxonomy_id'],
            $this->mappedTaxonData['taxon_id']
        );

        $preparedAttributes = $this->prepareUnifiedPrototypeAttributes(
            $prototypeAttributes
        );

        $this->sortPrototypeAttributes($preparedAttributes);

        return $preparedAttributes;
    }

    /**
     * @param array $prototypeAttributes
     *
     * @return array
     */
    protected function prepareUnifiedPrototypeAttributes(array $prototypeAttributes)
    {
        $returnData = [];

        foreach ($prototypeAttributes['properties'] as $property) {
            $returnData[$property['name']] = [
                'type' => self::ATTRIBUTE_TYPE_PROPERTY,
                'presentation' => $property['presentation'],
            ];
        }

        foreach ($prototypeAttributes['option_types'] as $optionType) {
            $returnData[$optionType['name']] = [
                'type' => self::ATTRIBUTE_TYPE_OPTION_VALUE,
                'presentation' => $optionType['presentation'],
                'options' => [],
            ];

            foreach ($optionType['spree_option_values'] as $option) {
                $returnData[$optionType['name']]['options'][$option['name']] = $option['presentation'];
            }
        }

        return $returnData;
    }

    /**
     * @param array $prototypeAttributes
     *
     * @return array
     */
    protected function sortPrototypeAttributes(array &$prototypeAttributes)
    {
        uasort($prototypeAttributes, function ($attribute1, $attribute2) {
            return strcoll($attribute1['presentation'], $attribute2['presentation']);
        });
    }

    /**
     * @return Mage_Catalog_Model_Resource_Eav_Attribute[]
     *
     * @throws \RuntimeException When no category to map
     */
    public function getCategoryProductsAttributes()
    {
        if (!$this->isCategoryFound()) {
            throw new \RuntimeException('No category to map');
        }

        return $this->mapper->getCategoryProductsAttributes($this->categoryToMap);
    }

    /**
     * @return string
     */
    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    /**
     * @return ArenaPl_Magento_Block_Categoryattributes
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'save_button',
            $this->getLayout()
                ->createBlock('adminhtml/widget_button')
                ->setData([
                    'label'     => 'Zapisz',
                    'onclick'   => 'arenaplSaveCategoryAttributes();',
                    'class'     => 'save pull-right',
                ])
            );

        return parent::_prepareLayout();
    }
}
