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

class Danslo_ApiImport_Model_Resource_Index_Entity_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    /**
     * Stores the index type.
     *
     * @var int
     */
    protected $_indexType = null;

    /**
     * Initialize resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('api_import/index_entity');
    }

    /**
     * Sets the index type.
     *
     * @param int $indexType
     * @return Danslo_ApiImport_Model_Resource_Index_Entity_Collection
     */
    public function setIndexType($indexType)
    {
        if ($this->_indexType === null) {
            $this->_indexType = $indexType;
        }
        return $this;
    }

    /**
     * Add the index type filter before loading.
     *
     * @return Danslo_ApiImport_Model_Resource_Index_Entity_Collection
     */
    protected function _beforeLoad()
    {
        if ($this->_indexType === null) {
            Mage::throwException('Did not specify an index type.');
        } else {
            parent::_beforeLoad();
            $this->addFieldToFilter('index_type', $this->_indexType);
        }
        return $this;
    }

    /**
     * Indexes product stock.
     *
     * @param array $entityIds
     * @return Mage_CatalogInventory_Model_Resource_Indexer_Stock
     */
    protected function _indexStock(&$entityIds)
    {
        return Mage::getResourceSingleton('cataloginventory/indexer_stock')->catalogProductMassAction(
            $this->_getIndexEvent($entityIds, 'reindex_stock_product_ids')
        );
    }

    /**
     * Indexes product price.
     *
     * @param array $entityIds
     * @return Mage_Catalog_Model_Resource_Product_Indexer_Price
     */
    protected function _indexPrice(&$entityIds)
    {
        return Mage::getResourceSingleton('catalog/product_indexer_price')->catalogProductMassAction(
            $this->_getIndexEvent($entityIds, 'reindex_price_product_ids')
        );
    }

    /**
     * Indexes product category relation.
     *
     * @param array $entityIds
     * @return Mage_Catalog_Model_Resource_Category_Indexer_Product
     */
    protected function _indexCategory(&$entityIds)
    {
        return Mage::getResourceSingleton('catalog/category_indexer_product')->catalogProductMassAction(
            $this->_getIndexEvent($entityIds, 'product_ids')
        );
    }

    /**
     * Indexes product EAV attributes.
     *
     * @param array $entityIds
     * @return Mage_Catalog_Model_Resource_Product_Indexer_Eav
     */
    protected function _indexEav(&$entityIds)
    {
        return Mage::getResourceSingleton('catalog/product_indexer_eav')->catalogProductMassAction(
            $this->_getIndexEvent($entityIds, 'reindex_eav_product_ids')
        );
    }

    /**
     * Generates an index event based on imported entity IDs.
     *
     * @param array $entityIds
     * @return Mage_Index_Model_Event
     */
    protected function _getIndexEvent(&$entityIds, $entityKey)
    {
        // Generate a fake mass update event that we pass to our indexers.
        return Mage::getModel('index/event')->setNewData(array($entityKey => &$entityIds));
    }

    /**
     * Indexes product search.
     *
     * @param array $entityIds
     * @return Mage_CatalogSearch_Model_Resource_Fulltext
     */
    protected function _indexSearch(&$entityIds)
    {
        return Mage::getResourceSingleton('catalogsearch/fulltext')->rebuildIndex(null, $entityIds);
    }

    /**
     * Indexes product URL rewrites.
     *
     * @param array $entityIds
     * @return Danslo_ApiImport_Model_Resource_Index_Entity_Collection
     */
    protected function _indexRewrite(&$entityIds)
    {
        // Only generate URL rewrites when this module is enabled.
        $indexer = Mage::getResourceSingleton('ecomdev_urlrewrite/indexer');
        if ($indexer) {
            return $indexer->updateProductRewrites($entityIds);
        }
        return $this;
    }

    /**
     * Gets the entity from every row in the collection.
     *
     * @return array
     */
    protected function _getEntityIds()
    {
        return $this->getColumnValues('entity_id');
    }

    /**
     * Delete everything so we don't process it again.
     *
     * @return Danslo_ApiImport_Model_Resource_Index_Entity_Collection
     */
    protected function _deleteAll(&$entityIds)
    {
        $this->getConnection()->delete($this->getMainTable(), array(
            'entity_id IN(?)' => $entityIds,
            'index_type = ?'  => $this->_indexType
        ));
        return $this;
    }

    /**
     * Indexes all entities for this index type.
     *
     * @return boolean
     */
    public function indexAll()
    {
        // Obtain all entity ids.
        $entityIds = $this->_getEntityIds();

        // Find an index method to call.
        $indexers = Mage::helper('api_import/index')->getIndexers();
        $method   = '_index' . ucfirst($indexers[$this->_indexType]);
        if (!method_exists($this, $method)) {
            Mage::throwException(sprintf('Index method does not exist: %s', $method));
        }

        // Attempt to index.
        try {
            $this->{$method}($entityIds);
        } catch (Mage_Exception $e) {
            Mage::logException($e);
            return false;
        }

        // If all went good, we can the entities from database.
        $this->_deleteAll($entityIds);
        return true;
    }

}