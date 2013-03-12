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

    /**
     * Indexes product stock.
     *
     * @param Mage_Index_Model_Event $event
     * @return Mage_CatalogInventory_Model_Resource_Indexer_Stock
     */
    protected function _indexStock(&$event)
    {
        return Mage::getResourceSingleton('cataloginventory/indexer_stock')->catalogProductMassAction($event);
    }

    /**
     * Indexes product price.
     *
     * @param Mage_Index_Model_Event $event
     * @return Mage_Catalog_Model_Resource_Product_Indexer_Price
     */
    protected function _indexPrice(&$event)
    {
        return Mage::getResourceSingleton('catalog/product_indexer_price')->catalogProductMassAction($event);
    }

    /**
     * Indexes product category relation.
     *
     * @param Mage_Index_Model_Event $event
     * @return Mage_Catalog_Model_Resource_Category_Indexer_Product
     */
    protected function _indexCategoryRelation(&$event)
    {
        return Mage::getResourceSingleton('catalog/category_indexer_product')->catalogProductMassAction($event);
    }

    /**
     * Indexes product EAV attributes.
     *
     * @param Mage_Index_Model_Event $event
     * @return Mage_Catalog_Model_Resource_Product_Indexer_Eav
     */
    protected function _indexEav(&$event)
    {
        return Mage::getResourceSingleton('catalog/product_indexer_eav')->catalogProductMassAction($event);
    }

    /**
     * Indexes product search.
     *
     * @param array $productIds
     * @return Mage_CatalogSearch_Model_Resource_Fulltext
     */
    protected function _indexSearch(&$productIds)
    {
        return Mage::getResourceSingleton('catalogsearch/fulltext')->rebuildIndex(null, $productIds);
    }

    /**
     * Indexes product URL rewrites.
     *
     * @param array $productIds
     * @return Danslo_ApiImport_Model_Observer
     */
    protected function _indexProductRewrites(&$productIds)
    {
        // Only generate URL rewrites when this module is enabled.
        $indexer = Mage::getResourceSingleton('ecomdev_urlrewrite/indexer');
        if ($indexer) {
            return $indexer->updateProductRewrites($productIds);
        }
        return $this;
    }

    /**
     * Indexes category URL rewrites.
     *
     * @param array $categoryIds
     * @return Danslo_ApiImport_Model_Observer
     */
    protected function _indexCategoryRewrites(&$categoryIds)
    {
        // Only generate URL rewrites when this module is enabled.
        $indexer = Mage::getResourceSingleton('ecomdev_urlrewrite/indexer');
        if ($indexer) {
            return $indexer->updateCategoryRewrites($categoryIds);
        }
        return $this;
    }

    /**
     * Generates an index event based on imported entity IDs.
     *
     * @param array $entityIds
     * @return Mage_Index_Model_Event
     */
    protected function _getIndexEvent(&$entityIds)
    {
        // Generate a fake mass update event that we pass to our indexers.
        $event = Mage::getModel('index/event');
        $event->setNewData(array(
            'reindex_price_product_ids' => &$entityIds, // for product_indexer_price
            'reindex_stock_product_ids' => &$entityIds, // for indexer_stock
            'product_ids'               => &$entityIds, // for category_indexer_product
            'reindex_eav_product_ids'   => &$entityIds  // for product_indexer_eav
        ));
        return $event;
    }

    /**
     * Partial category index after import.
     *
     * @param Varien_Event_Observer $observer
     * @return boolean
     */
    public function indexCategories($observer)
    {
        // We need to flatten the entities because category import passes multidimensional.
        $entityIds = array();
        foreach ($observer->getEntities() as $rootCategory) {
            foreach ($rootCategory as $category) {
                if ($category['entity_id'] !== null) {
                    $entityIds[] = $category['entity_id'];
                }
            }
        }
        if (!count($entityIds)) {
            return false;
        }

        // Index our category entities.
        try {
            if (Mage::getStoreConfig('api_import/import_settings/enable_rewrite_index')) {
                $this->_indexCategoryRewrites($entityIds);
            }
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
        return true;
    }

    /**
     * Partial product index after import.
     *
     * @param Varien_Event_Observer $observer
     * @return boolean
     */
    public function indexProducts($observer)
    {
        // Obtain all imported entity IDs.
        $entityIds = array();
        foreach ($observer->getEntities() as $entity) {
            $entityIds[] = $entity['entity_id'];
        }
        if (!count($entityIds)) {
            return false;
        }

        // Index our product entities.
        $event = $this->_getIndexEvent($entityIds);
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
                $this->_indexProductRewrites($entityIds);
            }
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
        return true;
    }

}
