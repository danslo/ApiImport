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

class Danslo_ApiImport_Model_Observer
{

    protected function _indexStock(&$event)
    {
        return Mage::getResourceSingleton('cataloginventory/indexer_stock')->catalogProductMassAction($event);
    }

    protected function _indexPrice(&$event)
    {
        return Mage::getResourceSingleton('catalog/product_indexer_price')->catalogProductMassAction($event);
    }

    protected function _indexCategoryRelation(&$event)
    {
        return Mage::getResourceSingleton('catalog/category_indexer_product')->catalogProductMassAction($event);
    }

    protected function _indexEav(&$event)
    {
        return Mage::getResourceSingleton('catalog/product_indexer_eav')->catalogProductMassAction($event);
    }

    protected function _indexSearch(&$productIds)
    {
        return Mage::getResourceSingleton('catalogsearch/fulltext')->rebuildIndex(null, $productIds);
    }

    protected function _indexRewrites(&$productIds)
    {
        /*
         * Only generate URL rewrites when this module is enabled.
         */
        $indexer = Mage::getResourceSingleton('ecomdev_urlrewrite/indexer');
        if ($indexer) {
            return $indexer->updateProductRewrites($productIds);
        }
        return $this;
    }

    public function indexProducts($observer)
    {
        /*
         * Obtain all imported entity IDs.
         */
        $entityIds = array();
        foreach ($observer->getEntities() as $entity) {
            $entityIds[] = $entity['entity_id'];
        }

        /*
         * Generate a fake mass update event that we pass to our indexers.
         */
        $event = Mage::getModel('index/event');
        $event->setNewData(array(
            'reindex_price_product_ids' => &$entityIds, // for product_indexer_price
            'reindex_stock_product_ids' => &$entityIds, // for indexer_stock
            'product_ids'               => &$entityIds, // for category_indexer_product
            'reindex_eav_product_ids'   => &$entityIds  // for product_indexer_eav
        ));

        /*
         * Index our product entities.
         */
        try {
            if (Mage::getStoreConfig('api_import/import_settings/enable_stock_index')) {
                $this->_indexStock($event);
            }
            if (Mage::getStoreConfig('api_import/import_settings/enable_price_index')) {
                $this->_indexPrice($event);
            }
            if (Mage::getStoreConfig('api_import/import_settings/enable_category_relation_index')) {
                $this->_indexCategoryRelation($event);
            }
            if (Mage::getStoreConfig('api_import/import_settings/enable_attribute_index')) {
                $this->_indexEav($event);
            }
            if (Mage::getStoreConfig('api_import/import_settings/enable_search_index')) {
                $this->_indexSearch($entityIds);
            }
            if (Mage::getStoreConfig('api_import/import_settings/enable_rewrite_index')) {
                $this->_indexRewrites($entityIds);
            }
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

}
