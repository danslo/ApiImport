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

class Danslo_ApiImport_Model_Import_Entity_Customer
    extends Mage_ImportExport_Model_Import_Entity_Customer
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
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->_dataSourceModel = Danslo_ApiImport_Model_Import::getDataSourceModel();
        $this->_addressEntity   = Mage::getModel('api_import/import_entity_customer_address', $this);
    }

    /**
     * Adds events before and after importing.
     *
     * @return boolean
     */
    public function _importData()
    {
        Mage::dispatchEvent($this->_eventPrefix . '_before_import', array(
            'data_source_model' => $this->_dataSourceModel,
            'entity_model'      => $this
        ));
        $result = parent::_importData();
        Mage::dispatchEvent($this->_eventPrefix . '_after_import', array(
            'entities'      => $this->_newCustomers,
            'entity_model'  => $this
        ));
        return $result;
    }
    
    /**
     * Get old customers.
     * 
     * @return array
     */
    public function getOldCustomers()
    {
        return $this->_oldCustomers;
    }

}
