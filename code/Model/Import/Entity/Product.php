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
    
    public function __construct() {
        $entityType             = Mage::getSingleton('eav/config')->getEntityType($this->getEntityTypeCode());
        $this->_entityTypeId    = $entityType->getEntityTypeId();
        $this->_connection      = Mage::getSingleton('core/resource')->getConnection('write');
        $this->_dataSourceModel = Danslo_ApiImport_Model_Import::getDataSourceModel();

        $this->_initWebsites()
            ->_initStores()
            ->_initAttributeSets()
            ->_initTypeModels()
            ->_initCategories()
            ->_initSkus()
            ->_initCustomerGroups();
    }
    
    protected function _indexEntities() {
        /*
         * Run some of the indexers for newly imported entities.
         */
        $entities = array();
        foreach($this->_newSku as $sku) {
            $entities[] = $sku['entity_id'];
        }
        
        /*
         * Set up transport object containing entities.
         */
        $transport = Mage::getModel('index/event');
        $transport->setNewData(array(
            'product_ids'               => $entities,   // for category_indexer_product
            'reindex_price_product_ids' => $entities    // for product_indexer_price
        ));

        /*
         * TODO: Some error handling.
         */
        Mage::getResourceSingleton('catalog/category_indexer_product')->catalogProductMassAction($transport);
        Mage::getResourceSingleton('catalog/product_indexer_price')->catalogProductMassAction($transport);

        return $this;
    }
    
    public function _importData() {
        $result = parent::_importData();
        if($result) {
            $this->_indexEntities();
        }
        return $result;
    }
    
}