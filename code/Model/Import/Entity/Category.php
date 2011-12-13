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

class Danslo_ApiImport_Model_Import_Entity_Category
    extends Mage_ImportExport_Model_Import_Entity_Abstract
{

    protected $_oldCategories = array();

    public function __construct() {
        parent::__construct();
        $this->_dataSourceModel = Danslo_ApiImport_Model_Import::getDataSourceModel();
        $this->_initCategories();
    }

    protected function _importData() {
        return $this->_saveCategories();
    }

    protected function _initCategories() {
        $table   = Mage::getSingleton('core/resource')->getTableName('catalog/category');
        $columns = array('entity_id', 'attribute_set_id', 'parent_id', 'path', 'position', 'level', 'children_count');
        $select  = $this->_connection->select()->from($table, $columns);
        $this->_oldCategories = $this->_connection->fetchAll($select);
        return $this;
    }

    protected function _saveCategories() {
        while($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach($bunch as $rowNum => $rowData) {
                if(!$this->validateRow($rowData, $rowNum)) {
                    continue;
                }
            }
        }

        /*
         * TODO: Actually save categories.
         */
        return $this;
    }

    public function getEntityTypeCode() {
        return 'catalog_category';
    }

    public function validateRow(array $rowData, $rowNum) {
        /*
         * TODO: Actually implement category validation.
         */
        return true;
    }

}