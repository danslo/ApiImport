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
        $entityType = Mage::getSingleton('Mage_Eav_Model_Config')->getEntityType($this->getEntityTypeCode());
        $this->_entityTypeId    = $entityType->getEntityTypeId();
        $this->_dataSourceModel = Danslo_ApiImport_Model_Import::getDataSourceModel();
        $this->_connection      = Mage::getSingleton('Mage_Core_Model_Resource')->getConnection('write');

        $this->_optionEntity = isset($data['option_entity']) ? $data['option_entity']
            : Mage::getModel('Mage_ImportExport_Model_Import_Entity_Product_Option',
                array('data' => array('product_entity' => $this))
            );

        $this->_importAttributes()
             ->_initWebsites()
             ->_initStores()
             ->_initAttributeSets()
             ->_initTypeModels()
             ->_initCategories()
             ->_initSkus()
             ->_initCustomerGroups();
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
        $productAttributes = Mage::getModel('Mage_Eav_Model_Entity_Type')->loadByCode($this->getEntityTypeCode())
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
        Mage::dispatchEvent($this->_eventPrefix . '_before_import', array('data_source_model' => $this->_dataSourceModel));
        $result = parent::_importData();
        Mage::dispatchEvent($this->_eventPrefix . '_after_import', array('entities' => $this->_newSku));
        return $result;
    }

}
