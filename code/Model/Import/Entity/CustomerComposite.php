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

class Danslo_ApiImport_Model_Import_Entity_CustomerComposite
    extends Mage_ImportExport_Model_Import_Entity_CustomerComposite
{

    /**
     * Prepended to all events fired in this class.
     *
     * @var string
     */
    protected $_eventPrefix = 'api_import_entity_customer';

    /**
     * Sets the proper data source model and adress model.
     *
     * @param  array $data
     * @return void
     */
    public function __construct(array $data = array())
    {
        parent::__construct($data);
        $this->_dataSourceModel = Danslo_ApiImport_Model_Import::getDataSourceModel();
        $this->_addressEntity   = Mage::getModel('Danslo_ApiImport_Model_Import_Entity_Eav_Customer_Address', array('data' => $data));
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
        Mage::dispatchEvent($this->_eventPrefix . '_after_import', array('entities' => $this->_newCustomers));
        return $result;
    }

}
