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

class Danslo_ApiImport_Model_Import_Entity_Product
    extends Mage_ImportExport_Model_Import_Entity_Product
{

    /**
     * Prepended to all events fired in this class.
     *
     * @var string
     */
    protected $_eventPrefix = 'api_import_entity_product';

    /**
     * Sets the proper data source model and makes sure dropdown options are imported.
     *
     * @return void
     */
    public function __construct()
    {
        $entityType = Mage::getSingleton('eav/config')->getEntityType($this->getEntityTypeCode());
        $this->_entityTypeId    = $entityType->getEntityTypeId();
        $this->_dataSourceModel = Danslo_ApiImport_Model_Import::getDataSourceModel();
        $this->_connection      = Mage::getSingleton('core/resource')->getConnection('write');

        $this->_importAttributes()
             ->_initWebsites()
             ->_initStores()
             ->_initAttributeSets()
             ->_initTypeModels()
             ->_initCategories()
             ->_initSkus()
             ->_initCustomerGroups()
             ->_initOldData();
    }

    /**
     * When a special import behavior is detected, merge oldSku into new data.
     * This allows us to only specify the sku during the import, allowing us to skip
     * specifying attribute sets, website code and product type.
     * This is ideal for simple updates like stock.
     *
     * @return Danslo_ApiImport_Model_Import_Entity_Product
     */
    protected function _initOldData()
    {
        if ($this->_dataSourceModel->getBehavior() == Danslo_ApiImport_Model_Import::BEHAVIOR_STOCK) {
            $entities = $this->_dataSourceModel->getEntities();
            foreach ($entities as $id => $entity) {
                if (isset($entity[self::COL_SKU]) && isset($this->_oldSku[$entity[self::COL_SKU]])) {
                    $entities[$id] = $entity + array(
                        self::COL_TYPE     => $this->_oldSku[$entity[self::COL_SKU]]['type_id'],
                        self::COL_ATTR_SET => $this->_attrSetIdToName[$this->_oldSku[$entity[self::COL_SKU]]['attr_set_id']]
                    );
                }
            }
            $this->_dataSourceModel->setEntities($entities);
        }
        return $this;
    }

    /**
     * Import behavior getter.
     *
     * @return string
     */
    public function getBehavior()
    {
        if (!isset($this->_parameters['behavior'])
            || ($this->_parameters['behavior'] != Mage_ImportExport_Model_Import::BEHAVIOR_APPEND
            && $this->_parameters['behavior'] != Mage_ImportExport_Model_Import::BEHAVIOR_REPLACE
            && $this->_parameters['behavior'] != Mage_ImportExport_Model_Import::BEHAVIOR_DELETE
            && $this->_parameters['behavior'] != Danslo_ApiImport_Model_Import::BEHAVIOR_STOCK)) {
            return Mage_ImportExport_Model_Import::getDefaultBehavior();
        }
        return $this->_parameters['behavior'];
    }

    /**
     * Gets the internal category array used for category mapping.
     *
     * @return array
     */
    public function getCategories()
    {
        return $this->_categories;
    }

    /**
     * Gets the internal store code to ID mapping, we need them for bundles.
     *
     * @return array
     */
    public function getStores()
    {
        return $this->_storeCodeToId;
    }

    /**
     * Set uploader
     *
     * Overcome the hardcoded $this->_fileUploader in parent::_getUploader()
     *
     * @param Mage_ImportExport_Model_Import_Uploader $uploader
     * @return Danslo_ApiImport_Model_Import_Entity_Product
     */
    public function setUploader(Mage_ImportExport_Model_Import_Uploader $uploader)
    {
        $this->_fileUploader = $uploader;
        return $this;
    }

    /**
     * Imports dropdown options for select/multiselect attributes.
     *
     * @return Danslo_ApiImport_Model_Import_Entity_Product
     */
    protected function _importAttributes()
    {
        $productAttributes = Mage::getModel('eav/entity_type')->loadByCode($this->getEntityTypeCode())
            ->getAttributeCollection()
            ->addFieldToFilter('frontend_input', array('select', 'multiselect'))
            ->addFieldToFilter('is_user_defined', true);

        foreach ($productAttributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $sourceOptions = $attribute->getSource()->getAllOptions(false);

            $options = array();
            foreach ($this->_dataSourceModel->getEntities() as $rowData) {
                if (isset($rowData[$attributeCode]) && strlen(trim($rowData[$attributeCode]))) {
                    $optionExists = false;
                    foreach ($sourceOptions as $sourceOption) {
                        if ($rowData[$attributeCode] == $sourceOption['label']) {
                            $optionExists = true;
                            break;
                        }
                    }
                    if (!$optionExists) {
                        $options['value']['option_' . $rowData[$attributeCode]][0] = $rowData[$attributeCode];
                    }
                }
            }
            if (!empty($options)) {
                $attribute->setOption($options)->save();
            }
        }
        $this->_dataSourceModel->getIterator()->rewind();

        return $this;
    }

    /**
     * Adds events before and after importing.
     *
     * @return boolean
     */
    public function _importData()
    {
        Mage::dispatchEvent($this->_eventPrefix . '_before_import', array(
            'entity_model'      => $this,
            'data_source_model' => $this->_dataSourceModel,
            'uploader'          => $this->_getUploader()
        ));

        // Do the actual import.
        parent::_importData();

        Mage::dispatchEvent($this->_eventPrefix . '_after_import', array(
            'entity_model' => $this,
            'entities'     => $this->_newSku
        ));
        return $this->_newSku;
    }

    /**
     * Preload stock data for products that we are importing.
     * This prevents excessive amounts of stock_item model loads with large stock updates.
     *
     * @return Danslo_ApiImport_Model_Import_Entity_Product
     */
    protected function _getStockItemData()
    {
        // Grab stock items for newSku.
        $productIds = array_map(function($e) { return $e['entity_id']; }, $this->_newSku);
        $stockItemCollection = Mage::getModel('cataloginventory/stock_item')
            ->getCollection()
            ->addFieldToFilter('product_id', array('in' => $productIds));

        // Index them by product ID.
        $stockItemData = array();
        foreach ($stockItemCollection as $stockItem) {
            $stockItemData[$stockItem['product_id']] = $stockItem;
        }
        return $stockItemData;
    }

    /**
     * Stock item saving.
     *
     * @return Mage_ImportExport_Model_Import_Entity_Product
     */
    protected function _saveStockItem()
    {
        $defaultStockData = array(
            'manage_stock'                  => 1,
            'use_config_manage_stock'       => 1,
            'qty'                           => 0,
            'min_qty'                       => 0,
            'use_config_min_qty'            => 1,
            'min_sale_qty'                  => 1,
            'use_config_min_sale_qty'       => 1,
            'max_sale_qty'                  => 10000,
            'use_config_max_sale_qty'       => 1,
            'is_qty_decimal'                => 0,
            'backorders'                    => 0,
            'use_config_backorders'         => 1,
            'notify_stock_qty'              => 1,
            'use_config_notify_stock_qty'   => 1,
            'enable_qty_increments'         => 0,
            'use_config_enable_qty_inc'     => 1,
            'qty_increments'                => 0,
            'use_config_qty_increments'     => 1,
            'is_in_stock'                   => 0,
            'low_stock_date'                => null,
            'stock_status_changed_auto'     => 0
        );

        $entityTable = Mage::getResourceModel('cataloginventory/stock_item')->getMainTable();
        $helper      = Mage::helper('catalogInventory');

        // This column was added in migration 1.6.0.0.1-1.6.0.0.2, preserve backwards compatibility.
        if ($this->_connection->tableColumnExists($entityTable, 'is_decimal_divided')) {
            $defaultStockData['is_decimal_divided'] = 0;
        }

        // Do this in advance to prevent excessive loads.
        $stockItemData = $this->_getStockItemData();

        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $stockData = array();

            // Format bunch to stock data rows
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->isRowAllowedToImport($rowData, $rowNum)) {
                    continue;
                }
                // only SCOPE_DEFAULT can contain stock data
                if (self::SCOPE_DEFAULT != $this->getRowScope($rowData)) {
                    continue;
                }

                /**
                 * Line below is a core bugfix, please see the following github change;
                 * https://github.com/mageplus/mageplus/commit/89a7362a3907a9fa9ff3d435394dc28b9fb42f95
                 */
                $row = array();
                $row['product_id'] = $this->_newSku[$rowData[self::COL_SKU]]['entity_id'];
                $row['stock_id'] = 1;

                /** @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
                if (isset($stockItemData[$row['product_id']])) {
                    $stockItem = $stockItemData[$row['product_id']];
                } else {
                    $stockItem = Mage::getModel('cataloginventory/stock_item');
                }
                $existStockData = $stockItem->getData();

                $row = array_merge(
                    $defaultStockData,
                    array_intersect_key($existStockData, $defaultStockData),
                    array_intersect_key($rowData, $defaultStockData),
                    $row
                );

                $stockItem->setData($row);

                if ($helper->isQty($this->_newSku[$rowData[self::COL_SKU]]['type_id'])) {
                    if ($stockItem->verifyNotification()) {
                        $stockItem->setLowStockDate(Mage::app()->getLocale()
                            ->date(null, null, null, false)
                            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)
                        );
                    }
                    $stockItem->setStockStatusChangedAutomatically((int) !$stockItem->verifyStock());
                } else {
                    $stockItem->setQty(0);
                }
                $stockData[] = $stockItem->unsetOldData()->getData();
            }

            // Insert rows
            if ($stockData) {
                $this->_connection->insertOnDuplicate($entityTable, $stockData);
            }
        }
        return $this;
    }

    /**
     * Returns an object for upload a media files
     */
    protected function _getUploader()
    {
        if (is_null($this->_fileUploader)) {
            $this->_fileUploader = new Danslo_ApiImport_Model_Import_Uploader();

            $this->_fileUploader->init();

            $tmpDir     = Mage::getConfig()->getOptions()->getMediaDir() . '/import';
            $destDir    = Mage::getConfig()->getOptions()->getMediaDir() . '/catalog/product';
            if (!is_writable($destDir)) {
                @mkdir($destDir, 0777, true);
            }
            if (!$this->_fileUploader->setTmpDir($tmpDir)) {
                Mage::throwException("File directory '{$tmpDir}' is not readable.");
            }
            if (!$this->_fileUploader->setDestDir($destDir)) {
                Mage::throwException("File directory '{$destDir}' is not writable.");
            }
        }
        return $this->_fileUploader;
    }
}
