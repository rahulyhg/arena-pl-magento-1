<?php

class ArenaPl_Magento_Block_Categories extends Mage_Core_Block_Template
{
    /**
     * Separator between select name parts.
     */
    const CATEGORY_SEPARATOR = ' -> ';

    /**
     * @var ArenaPl_Magento_Model_Mapper
     */
    protected $mapper;

    /**
     * @var Mage_Catalog_Model_Category
     */
    protected $category;

    /**
     * @var ArenaPl_Magento_Helper_Data
     */
    protected $helper;

    /**
     * @var array
     */
    protected $baseCategories = null;

    public function _construct()
    {
        parent::_construct();

        $this->mapper = Mage::getSingleton('arenapl_magento/mapper');
        $this->helper = Mage::helper('arenapl_magento');
    }

    /**
     * @param Mage_Catalog_Model_Category $category
     *
     * @return self
     */
    public function setCategory(Mage_Catalog_Model_Category $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    public function getAllCategories()
    {
        /* @var $category Mage_Catalog_Model_Category */
        $category = Mage::getModel('catalog/category');

        /* @var $collection Mage_Catalog_Model_Resource_Category_Collection */
        $collection = $category->getCollection();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToSort('path');

        return $collection;
    }

    /**
     * @return string
     */
    public function displayMagentoCategoryString()
    {
        $elements = [];
        foreach ($this->category->getParentIds() as $id) {
            $elements[] = Mage::getModel('catalog/category')->load($id)->getName();
        }

        $elements[] = $this->category->getName();

        return implode(self::CATEGORY_SEPARATOR, $elements);
    }

    /**
     * @return bool
     */
    public function hasMappedTaxon()
    {
        return $this->mapper->hasMappedTaxon($this->category);
    }

    /**
     * @return array
     */
    public function getCategoryTaxonData()
    {
        $data = $this->mapper->getMappedArenaTaxon($this->category);

        return is_array($data) ? $data : [];
    }

    /**
     * @return array
     */
    public function getBaseTaxonomies()
    {
        if ($this->baseCategories === null) {
            $this->baseCategories = $this->mapper->getBaseTaxons();
        }

        return $this->baseCategories;
    }

    /**
     * @param array
     *
     * @return array
     */
    public function getTaxonomiesSelect(array $taxonData)
    {
        $cacheKey = sprintf(
            'arenapl_taxonomies_select_%s',
            crc32(serialize($taxonData))
        );

        return $this->helper->cacheExpensiveCall($cacheKey, function () use ($taxonData) {
            $baseTaxon = $this->mapper->getBaseTaxon($taxonData);
            $taxonomyTree = $this->mapper->getTaxonTree($baseTaxon);

            foreach ($taxonomyTree as &$taxon) {
                $taxon['name'] = $taxon['pretty_name'];
            }

            return $taxonomyTree;
        }, ['arenapl_categories'], 360000);
    }

    /**
     * @param array $taxonData
     *
     * @return array
     */
    public function getBaseTaxon(array $taxonData)
    {
        return $this->mapper->getBaseTaxon($taxonData);
    }

    /**
     * @param int $taxonomyId
     * @param int $taxonId
     *
     * @return array
     */
    public function getTaxonData($taxonomyId, $taxonId)
    {
        $data = $this->mapper->getTaxonData((int) $taxonomyId, (int) $taxonId);

        return is_array($data) ? $data : [];
    }

    /**
     * @return ArenaPl_Magento_Block_Categories
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'save_button',
            $this->getLayout()
                ->createBlock('adminhtml/widget_button')
                ->setData([
                    'label'     => 'Zapisz',
                    'onclick'   => 'arenaplSaveCategories();',
                    'class'     => 'save',
                ])
            );

        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }
}
