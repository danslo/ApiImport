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
        if (!Mage::helper('core')->isModuleEnabled('EcomDev_UrlRewrite')) {
            return $this;
        }

        return Mage::getResourceSingleton('ecomdev_urlrewrite/indexer')->updateProductRewrites($productIds);
    }

    /**
     * Indexes category URL rewrites.
     *
     * @param array $categoryIds
     * @return Danslo_ApiImport_Model_Observer
     */
    protected function _indexCategoryRewrites(&$categoryIds)
    {
        if (!Mage::helper('core')->isModuleEnabled('EcomDev_UrlRewrite')) {
            return $this;
        }

        return Mage::getResourceSingleton('ecomdev_urlrewrite/indexer')->updateCategoryRewrites($categoryIds);
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
     * Verify if information are set in the entity and correct for the given media attribute
     *
     * @param array  $entity
     * @param string $attribute
     *
     * @return bool
     */
    protected function _isImageToImport($entity, $attribute)
    {
        return (isset($entity["_media_image_content"]) ||  isset($entity[$attribute . '_content']) && !empty($entity[$attribute . '_content'])
            && isset($entity[$attribute]) && !empty($entity[$attribute])
            && is_string($entity[$attribute]) && is_string($entity[$attribute . '_content']));
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

    /**
     * Creates the media import folder.
     *
     * @return Danslo_ApiImport_Model_Observer
     */
    protected function _createMediaImportFolder()
    {
        $ioAdapter = new Varien_Io_File();
        $ioAdapter->checkAndCreateFolder(Mage::getConfig()->getOptions()->getMediaDir() . '/import');
        return $this;
    }

    /**
     * Import images before products
     *
     * @param Varien_Event_Observer $observer
     *
     * @return boolean
     */
    public function importMedia($observer)
    {
        $this->_createMediaImportFolder();

        $ioAdapter        = new Varien_Io_File();
        $entities         = $observer->getDataSourceModel()->getEntities();
        $uploader         = $observer->getUploader();
        $tmpImportFolder  = $uploader->getTmpDir();
        $attributes       = Mage::getResourceModel('catalog/product_attribute_collection')->getItems();
        $mediaAttr        = array();
        $mediaAttributeId = Mage::getModel('eav/entity_attribute')
            ->load('media_gallery', 'attribute_code')
            ->getAttributeId();

        foreach ($attributes as $attr) {
            if ($attr->getFrontendInput() === 'media_image') {
                $mediaAttr[] = $attr->getAttributeCode();
            }
        }

        /* Add generic image attribute. This allows uploading images to gallery even without a related attribute */
        $mediaAttr[] = "_media_image";

        foreach($entities as $key => $entity) {
            foreach ($mediaAttr as $attr) {
                if ($this->_isImageToImport($entity, $attr)) {
                    try {
                        $ioAdapter->open(array('path' => $tmpImportFolder));
                        $ioAdapter->write(end(explode('/', $entity[$attr])), base64_decode($entity[$attr . '_content']), 0666);

                        $entities[$key]['_media_attribute_id'] = $mediaAttributeId;
                        unset($entities[$key][$attr . '_content']);
                    } catch (Exception $e) {
                        Mage::throwException($e->getMessage());
                    }
                }
            }
        }
        $observer->getDataSourceModel()->setEntities($entities);

        return true;
    }
}
