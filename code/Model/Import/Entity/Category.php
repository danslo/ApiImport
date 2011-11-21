<?php

class Danslo_ApiImport_Model_Import_Entity_Category extends Mage_ImportExport_Model_Import_Entity_Abstract {

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