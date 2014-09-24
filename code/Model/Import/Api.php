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

class Danslo_ApiImport_Model_Import_Api
    extends Mage_Api_Model_Resource_Abstract
{

    /**
     * Cached import model.
     *
     * @var Mage_ApiImport_Model_Import
     */
    protected $_api;

    /**
     * Sets up the import model and loads area parts.
     *
     * @return void
     */
    public function __construct()
    {
        $this->_api = Mage::getModel('api_import/import');

        // Event part is not loaded by default for API.
        Mage::app()->loadAreaPart(Mage_Core_Model_App_Area::AREA_GLOBAL, Mage_Core_Model_App_Area::PART_EVENTS);
    }

    /**
     * Fires off the import process through the import model.
     *
     * @param array $entities
     * @param string $entityType
     * @param string $behavior
     * @return array
     */
    public function importEntities($entities, $entityType = null, $behavior = null)
    {
        $this->_setEntityTypeCode($entityType ? $entityType : Mage_Catalog_Model_Product::ENTITY);
        $this->_setBehavior($behavior ? $behavior : Mage_ImportExport_Model_Import::BEHAVIOR_REPLACE);

        $this->_api->getDataSourceModel()->setEntities($entities);
        try {
            $result = $this->_api->importSource();
        } catch(Mage_Core_Exception $e) {
            $this->_fault('import_failed', $e->getMessage());
        }

        return array($result);
    }

    /**
     * Import attributes and put them in attribute sets and attribute group
     *
     * @param array  $data
     * @param string $behavior
     *
     * @return true
     */
    public function importAttributes(array $data, $behavior = null)
    {
        if (null === $behavior) {
            $behavior = Mage_ImportExport_Model_Import::BEHAVIOR_APPEND;
        }

        $setup = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('catalog_product_attribute_set');
        $entityTypeId = 'catalog_product';

        foreach ($data as $attribute) {

            if (isset($attribute['attribute_code'])) {

                $attributeCode = $attribute['attribute_code'];
                unset($attribute['attribute_code']);

                if (Mage_ImportExport_Model_Import::BEHAVIOR_REPLACE === $behavior
                    || Mage_ImportExport_Model_Import::BEHAVIOR_APPEND === $behavior
                ) {
                    $setup->addAttribute($entityTypeId, $attributeCode, $attribute);
                } else if (Mage_ImportExport_Model_Import::BEHAVIOR_DELETE === $behavior) {
                    $setup->removeAttribute($entityTypeId, $attributeCode);
                }
            }
        }

        return true;
    }

    /**
     * Compute attributes sets and their groups and import them
     *
     * @param array  $data
     * @param string $behavior
     *
     * @return true
     */
    public function importAttributeSets(array $data, $behavior = null)
    {
        if (null === $behavior) {
            $behavior = Mage_ImportExport_Model_Import::BEHAVIOR_APPEND;
        }

        $setup = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('catalog_product_attribute_set');
        $entityTypeId = 'catalog_product';

        if (Mage_ImportExport_Model_Import::BEHAVIOR_DELETE === $behavior) {

            foreach ($data as $attributeSet) {
                $setup->removeAttributeSet($entityTypeId, $attributeSet['name']);
            }
        } else if (Mage_ImportExport_Model_Import::BEHAVIOR_REPLACE === $behavior
            || Mage_ImportExport_Model_Import::BEHAVIOR_APPEND === $behavior
        ) {
            $connexion = $setup->getConnection();
            $getOldGroupsQuery = $connexion
                ->select()
                ->from($setup->getTable('eav/attribute_group'))
                ->where('attribute_set_id = :attribute_set_id');

            foreach ($data as $attributeSet) {
                $attrSetName     = $attributeSet['name'];
                $sortOrder       = $attributeSet['sortOrder'];
                $attributeGroups = $attributeSet;
                unset($attributeGroups['name']);
                unset($attributeGroups['sortOrder']);

                $setup->addAttributeSet('catalog_product', $attrSetName, $sortOrder);
                $attrSetId = $setup->getAttributeSet($entityTypeId, $attrSetName, 'attribute_set_id');

                $bind = array('attribute_set_id' => $attrSetId);

                $currentGroups = [];
                foreach ($connexion->fetchAssoc($getOldGroupsQuery, $bind) as $attrGroup) {
                    $currentGroups[$attrGroup['attribute_group_name']] = $attrGroup['sort_order'];
                }

                $groupsToRemove = array_keys(array_diff_key($currentGroups, $attributeGroups));
                foreach ($groupsToRemove as $groupToRemoveName) {
                    unset($currentGroups[$groupToRemoveName]);
                    $setup->removeAttributeGroup($entityTypeId, $attrSetId, $groupToRemoveName);
                }

                $groupsToUpdate = array_diff_assoc($currentGroups, $attributeGroups);
                foreach ($groupsToUpdate as $groupToUpdateName => $groupSortOrder) {
                    unset($attributeGroups[$groupToUpdateName]);
                    $setup->updateAttributeGroup(
                        $entityTypeId,
                        $attrSetId,
                        $groupToUpdateName,
                        'sort_order',
                        $groupSortOrder
                    );
                }

                foreach ($attributeGroups as $groupName => $groupSortOrder) {
                    $setup->addAttributeGroup($entityTypeId, $attrSetId, $groupName, $groupSortOrder);
                }
            }
        }

        return true;
    }

    /**
     * Sets entity type in the source model.
     *
     * @param string $entityType
     * @return void
     */
    protected function _setEntityTypeCode($entityType)
    {
        try {
            $this->_api->getDataSourceModel()->setEntityTypeCode($entityType);
        } catch(Mage_Core_Exception $e) {
            $this->_fault('invalid_entity_type', $e->getMessage());
        }
    }

    /**
     * Sets import behavior in the source model.
     *
     * @param string $behavior
     * @return void
     */
    protected function _setBehavior($behavior)
    {
        try {
            $this->_api->getDataSourceModel()->setBehavior($behavior);
        } catch(Mage_Core_Exception $e) {
            $this->_fault('invalid_behavior', $e->getMessage());
        }
    }
}
