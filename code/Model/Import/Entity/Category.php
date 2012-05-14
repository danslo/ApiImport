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

    protected $_categories = array();
    protected $_defaultAttributeSetId;

    const COL_NAME   = 'name';
    const COL_PARENT = 'parent';

    const ERROR_INVALID_PARENT_CATEGORY = 'invalidParentCategory';

    public function __construct()
    {
        parent::__construct();
        $this->_dataSourceModel = Danslo_ApiImport_Model_Import::getDataSourceModel();
        $this->_initCategories()
             ->_initAttributeSet();
    }

    protected function _importData()
    {
        return $this->_saveCategories();
    }

    protected function _initAttributeSet()
    {
        $entityType = Mage::getSingleton('eav/config')->getEntityType($this->getEntityTypeCode());
        $this->_defaultAttributeSetId = $entityType->getDefaultAttributeSetId();
    }

    /*
     * Implementation is similar to product, but we start one level up.
     */
    protected function _initCategories()
    {
        $collection = Mage::getResourceModel('catalog/category_collection')->addNameToResult();
        foreach ($collection as $category) {
            $structure = explode('/', $category->getPath());
            $pathSize  = count($structure);
            if ($pathSize > 1) {
                $path = array();
                for ($i = 1; $i < $pathSize; $i++) {
                    $path[] = $collection->getItemById($structure[$i])->getName();
                }
                $this->_categories[implode('/', $path)] = $category->getId();
            }
        }
        return $this;
    }

    protected function _saveCategories()
    {
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entityRowsIn = array();
            $entityRowsUp = array();

            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->validateRow($rowData, $rowNum)) {
                    continue;
                }

                /*
                 * Entity phase.
                 */
                $rowPath = $rowData[self::COL_PARENT] . '/' . $rowData[self::COL_NAME];
                if (isset($this->_categories[$rowPath])) {
                    $entityRowsUp[] = array(
                        'updated_at' => now(),
                        'entity_id'  => $this->_categories[$rowPath]['entity_id']
                    );
                } else {
                    $entityRowsIn[$rowPath] = array(
                        'entity_type_id'   => $this->_entityTypeId,
                        'attribute_set_id' => $this->_defaultAttributeSetId,
                        'parent_id'        => $this->_categories[$rowData[self::COL_PARENT]]['entity_id'],
                        'created_at'       => now(),
                        'updated_at'       => now(),

                        /*
                         * TODO: These will require a bit more knowledge of surrounding categories.
                         */
                        'path'             => '',
                        'position'         => 0,
                        'level'            => 0,
                        'children_count'   => 0
                    );
                }
            }
        }
        $this->_saveCategoryEntity($entityRowsIn, $entityRowsUp);

        return $this;
    }

    protected function _saveCategoryEntity(array $entityRowsIn, array $entityRowsUp)
    {
        static $entityTable = null;

        if (!$entityTable) {
            $entityTable = Mage::getModel('api_import/import_proxy_category_resource')->getEntityTable();
        }
        if ($entityRowsUp) {
            $this->_connection->insertOnDuplicate(
                $entityTable,
                $entityRowsUp,
                array('updated_at')
            );
        }
        if ($entityRowsIn) {
            $this->_connection->insertMultiple($entityTable, $entityRowsIn);
            /*
             * TODO: Populate newCategories here.
             */
        }
        return $this;
    }

    public function getEntityTypeCode()
    {
        return 'catalog_category';
    }

    protected function _isParentCategoryValid($rowData, $rowNum)
    {
        if (!isset($this->_categories[$rowData[self::COL_PARENT]])) {
            $this->addRowError(self::ERROR_INVALID_PARENT_CATEGORY, $rowNum);
            return false;
        }
        return true;
    }

    public function validateRow(array $rowData, $rowNum)
    {
        $this->_isParentCategoryValid($rowData, $rowNum);
        return !isset($this->_invalidRows[$rowNum]);
    }

}
