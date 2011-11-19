<?php
/*
 * Copyright 2011 Daniel Sloof
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
*/

class Danslo_ApiImport_Model_Import_Entity_Product extends Mage_ImportExport_Model_Import_Entity_Product {
    
    protected $_attributes = null;
    
    public function __construct() {
        /*
         * Setup from the abstract model.
         */
        $entityType = Mage::getSingleton('eav/config')->getEntityType($this->getEntityTypeCode());
        $this->_entityTypeId    = $entityType->getEntityTypeId();
        $this->_dataSourceModel = Danslo_ApiImport_Model_Import::getDataSourceModel();
        $this->_connection      = Mage::getSingleton('core/resource')->getConnection('write');
        
        /*
         * Setup for product entities.
         */
        $this->_importAttributes() // Import non-existent attribute values.
            ->_initWebsites()
            ->_initStores()
            ->_initAttributeSets()
            ->_initTypeModels()
            ->_initCategories()
            ->_initSkus()
            ->_initCustomerGroups();
    }
    
    protected function _indexStock(&$event) {
        return Mage::getResourceSingleton('cataloginventory/indexer_stock')->catalogProductMassAction($event);
    }
    
    protected function _indexPrice(&$event) {
        return Mage::getResourceSingleton('catalog/product_indexer_price')->catalogProductMassAction($event);
    }
    
    protected function _indexCategoryRelation(&$event) {
        return Mage::getResourceSingleton('catalog/category_indexer_product')->catalogProductMassAction($event);
    }
    
    protected function _indexEav(&$event) {
        return Mage::getResourceSingleton('catalog/product_indexer_eav')->catalogProductMassAction($event);
    }
    
    protected function _indexSearch(&$productIds) {
        return Mage::getResourceSingleton('catalogsearch/fulltext')->rebuildIndex(null, $productIds);
    }
    
    protected function _indexRewrites(&$productIds) {
        $indexer = Mage::getResourceSingleton('ecomdev_urlrewrite/indexer');
        if($indexer) {
            return $indexer->updateProductRewrites($productIds);
        }
        return $this;
    }
    
    protected function _indexEntities() {
        /*
         * Run some of the indexers for newly imported entities.
         */
        $entityIds = array();
        foreach($this->_newSku as $sku) {
            $entityIds[] = $sku['entity_id'];
        }
        
        /*
         * Set up event object for transporting our product ids.
         */
        $event = Mage::getModel('index/event');
        $event->setNewData(array(
            'product_ids'               => &$entityIds, // for category_indexer_product
            'reindex_price_product_ids' => &$entityIds, // for product_indexer_price
            'reindex_stock_product_ids' => &$entityIds, // for indexer_stock
            'reindex_eav_product_ids'   => &$entityIds  // for product_indexer_eav
        ));

        /*
         * Rebuild indexes that are essential to basic functionality.
         */
        try {
            if(Mage::getStoreConfig('api_import/import_settings/enable_stock_index')) {
                $this->_indexStock($event);
            }
            if(Mage::getStoreConfig('api_import/import_settings/enable_price_index')) {
                $this->_indexPrice($event);
            }
            if(Mage::getStoreConfig('api_import/import_settings/enable_category_relation_index')) {
                $this->_indexCategoryRelation($event);
            }
            if(Mage::getStoreConfig('api_import/import_settings/enable_attribute_index')) {
                $this->_indexEav($event);
            }
            if(Mage::getStoreConfig('api_import/import_settings/enable_search_index')) {
                $this->_indexSearch($entityIds);
            }
            if(Mage::getStoreConfig('api_import/import_settings/enable_rewrite_index')) {
                $this->_indexRewrites($entityIds);
            }
        } 
        catch(Exception $e) {
            return false;
        }
        
        return $this;
    }
    
    protected function _initAttributes() {
        if($this->_attributes === null) {
            $productEntityType = Mage::getModel('eav/entity_type')->loadByCode($this->getEntityTypeCode());
            $productAttributes = $productEntityType->getAttributeCollection()
                    ->setFrontendInputTypeFilter('select')
                    ->addFieldToFilter('is_user_defined', true);

            /*
             * Group attributes by code for easier lookup.
             */
            foreach($productAttributes as $attribute) {
                $this->_attributes[$attribute->getAttributeCode()] = $attribute;
            }
        }
        
        return $this;
    }
    
    protected function _importAttributes() {
        /*
         * TODO: Support for multiple storeviews.
         */
        $this->_initAttributes();
        foreach($this->_attributes as $code => $attribute) {
            /*
             * Optionally add non-existent attributes.
             */
            $sourceOptions = $attribute->getSource()->getAllOptions(false);
            while($bunch = $this->_dataSourceModel->getNextBunch()) {
                foreach($bunch as $rowNum => $rowData) {
                    if(isset($rowData[$code])) {
                        $optionExists = false;
                        foreach($sourceOptions as $sourceOption) {
                            if($rowData[$code] == $sourceOption['label']) {
                                $optionExists = true;
                                break;
                            }
                        }
                        if(!$optionExists) {
                            $options['value'][$rowData[$code]][0] = $rowData[$code];
                        }
                    }
                }
            }
            /*
             * Save all attributes.
             */
            if(!empty($options)) {
                $attribute->setOption($options)->save();
            }
        }
        $this->_dataSourceModel->getIterator()->rewind();
        
        return $this;
    }

    public function _importData() {
        $result = parent::_importData();
        if($result) {
            $result = $this->_indexEntities();
        }
        return $result;
    }
    
}
