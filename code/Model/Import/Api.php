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
     * @var Mage_Catalog_Model_Resource_Eav_Mysql4_Setup
     */
    protected $_setup;

    /**
     * @var int
     */
    protected $_catalogProductEntityTypeId;

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

        $this->_init();

        if (Danslo_ApiImport_Model_Import::BEHAVIOR_DELETE_IF_NOT_EXIST === $behavior) {
            $this->_pruneAttributes($data);
        } else {

            foreach ($data as $attribute) {

                if (isset($attribute['attribute_id'])) {

                    $attributeCode = $attribute['attribute_id'];
                    unset($attribute['attribute_id']);

                    if (Mage_ImportExport_Model_Import::BEHAVIOR_REPLACE === $behavior
                        || Mage_ImportExport_Model_Import::BEHAVIOR_APPEND === $behavior
                    ) {
                        $this->_setup->addAttribute($this->_catalogProductEntityTypeId, $attributeCode, $attribute);
                    } elseif (Mage_ImportExport_Model_Import::BEHAVIOR_DELETE === $behavior) {
                        $this->_setup->removeAttribute($this->_catalogProductEntityTypeId, $attributeCode);
                    }
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

        $this->_init();

        if (Mage_ImportExport_Model_Import::BEHAVIOR_DELETE === $behavior) {
            $this->_removeAttributeSets($data);
        } elseif (Mage_ImportExport_Model_Import::BEHAVIOR_REPLACE === $behavior
            || Mage_ImportExport_Model_Import::BEHAVIOR_APPEND === $behavior
        ) {
            $this->_updateAttributeSets($data);
        } elseif (Danslo_ApiImport_Model_Import::BEHAVIOR_DELETE_IF_NOT_EXIST === $behavior) {
            $this->_pruneAttributeSets($data);
        }

        return true;
    }

    /**
     * Links attributes to attributes group and attribute sets
     *
     * @param array  $data
     * @param string $behavior
     *
     * @return bool
     */
    public function importAttributeAssociations(array $data, $behavior = null)
    {
        if (null === $behavior) {
            $behavior = Mage_ImportExport_Model_Import::BEHAVIOR_APPEND;
        }

        $this->_init();

        if (Mage_ImportExport_Model_Import::BEHAVIOR_DELETE === $behavior) {
            $this->_removeAttributeFromGroup($data);
        } elseif (Mage_ImportExport_Model_Import::BEHAVIOR_REPLACE === $behavior
            || Mage_ImportExport_Model_Import::BEHAVIOR_APPEND === $behavior
        ) {
            $this->_updateAttributeAssociations($data);
        } elseif (Danslo_ApiImport_Model_Import::BEHAVIOR_DELETE_IF_NOT_EXIST === $behavior) {
            $this->_pruneAttributesFromAttributeSets($data);
        }

        return true;
    }

    /**
     * Initialize parameters
     *
     * @return void
     */
    protected function _init()
    {
        $this->_setup = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('catalog_product_attribute_set');
        $this->_catalogProductEntityTypeId = $this->_setup->getEntityTypeId(Mage_Catalog_Model_Product::ENTITY);
    }

    /**
     * Remove attribute and group association in attribute sets
     *
     * @param array $data
     *
     * @return void
     */
    protected function _removeAttributeFromGroup(array $data)
    {
        $entityTypeId = $this->_catalogProductEntityTypeId;

        foreach ($data as $attribute) {
            $setId       = $this->_setup->getAttributeSetId($entityTypeId, $attribute['attribute_set_id']);
            $attributeId = $this->_setup->getAttributeId($entityTypeId, $attribute['attribute_id']);
            $groupId     = $this->_setup->getAttributeGroupId(
                $entityTypeId,
                $attribute['attribute_set_id'],
                $attribute['attribute_group_id']
            );

            $this->_setup->getConnection()->delete(
                $this->_setup->getTable('eav/entity_attribute'),
                array (
                    new Zend_Db_Expr('entity_type_id = ' . $entityTypeId),
                    new Zend_Db_Expr('attribute_set_id = ' . $setId),
                    new Zend_Db_Expr('attribute_id = ' . $attributeId),
                    new Zend_Db_Expr('attribute_group_id = ' . $groupId)
                )
            );
        }
    }

    /**
     * Remove given attributes if not exist in Magento
     *
     * @param array $data
     *
     * @return void
     */
    protected function _pruneAttributes(array $data)
    {
        $select = $this->_setup->getConnection()
            ->select()
            ->from($this->_setup->getTable('eav/attribute'))
            ->where('is_user_defined = 1');
        $magAttributes = $this->_setup->getConnection()->fetchAssoc($select);

        foreach ($magAttributes as $magAttribute) {

            $attributeFound = false;
            while ((list($key, $attribute) = each($data)) && $attributeFound === false) {
                if ($attribute['attribute_id'] === $magAttribute['attribute_code']) {
                    $attributeFound = true;
                }
            }
            reset($data);

            if (!$attributeFound) {
                $this->_setup->removeAttribute($this->_catalogProductEntityTypeId, $magAttribute['attribute_code']);
            }
        }
    }

    /**
     * Delete associations if they exist in magento but not in given data
     *
     * @param array $data
     *
     * @return array
     */
    protected function _pruneAttributesFromAttributeSets(array $data)
    {
        $entityTypeId = $this->_catalogProductEntityTypeId;
        $query = $this->_setup->getConnection()
            ->select()
            ->from($this->_setup->getTable('eav/entity_attribute'))
            ->where('entity_type_id = :entity_type_id');
        $bind = array('entity_type_id' => $this->_catalogProductEntityTypeId);

        $givenAssociations = [];
        foreach ($data as $attribute) {
            $setId = $this->_setup->getAttributeSetId($entityTypeId, $attribute['attribute_set_id']);
            $givenAssociations[] = array(
                'attribute_id'       => $this->_setup->getAttributeId($entityTypeId, $attribute['attribute_id']),
                'attribute_set_id'   => $setId,
                'attribute_group_id' => $this->_setup->getAttributeGroupId(
                    $entityTypeId,
                    $setId,
                    $attribute['attribute_group_id']
                )
            );

        }

        $deletedRows = [];
        foreach ($this->_setup->getConnection()->fetchAssoc($query, $bind) as $magAssociation) {

            $rowFound = false;
            while ((list($key, $association) = each($givenAssociations)) && $rowFound === false) {
                if ($association['attribute_id'] === $magAssociation['attribute_id']
                    && $association['attribute_set_id'] === $magAssociation['attribute_set_id']
                    && $association['attribute_group_id'] === $magAssociation['attribute_group_id']
                ) {
                    $rowFound = true;
                }
            }
            reset($givenAssociations);

            if (!$rowFound) {
                $deletedRows[$magAssociation['entity_attribute_id']] = $this->_setup->getConnection()
                    ->delete(
                        $this->_setup->getTable('eav/entity_attribute'),
                        new Zend_Db_Expr('entity_attribute_id = ' . $magAssociation['entity_attribute_id'])
                    );
            }
        }

        return $deletedRows;
    }

    /**
     * Update associations between attributes, attribute groups and attribute sets
     *
     * @param array $data
     *
     * @return void
     */
    protected function _updateAttributeAssociations(array $data)
    {
        foreach ($data as $attribute) {
            $this->_setup->addAttributeToGroup(
                $this->_catalogProductEntityTypeId,
                $attribute['attribute_set_id'],
                $attribute['attribute_group_id'],
                $attribute['attribute_id'],
                $attribute['sort_order']
            );
        }
    }

    /**
     * Remove attribute sets
     *
     * @param array $data
     *
     * @return void
     */
    protected function _removeAttributeSets(array $data)
    {
        foreach ($data as $attributeSet) {
            $this->_setup->removeAttributeSet('catalog_product', $attributeSet['attribute_set_name']);
        }
    }

    /**
     * Update attribute sets and groups
     *
     * @param array $data
     *
     * @return void
     */
    protected function _updateAttributeSets(array $data)
    {
        $entityTypeId = $this->_catalogProductEntityTypeId;
        foreach ($data as $attributeSet) {
            $attrSetName     = $attributeSet['attribute_set_name'];
            $sortOrder       = $attributeSet['sortOrder'];
            $attributeGroups = $attributeSet;
            unset($attributeGroups['attribute_set_name']);
            unset($attributeGroups['sortOrder']);

            $this->_setup->addAttributeSet($entityTypeId, $attrSetName, $sortOrder);

            $attrSetId = $this->_setup->getAttributeSet($entityTypeId, $attrSetName, 'attribute_set_id');

            $currentGroups = $this->_getAttributeGroups($attrSetId);

            $groupsToRemove = array_keys(array_diff_key($currentGroups, $attributeGroups));
            foreach ($groupsToRemove as $groupToRemoveName) {
                unset($currentGroups[$groupToRemoveName]);
            }

            foreach ($attributeGroups as $groupName => $groupSortOrder) {
                $this->_setup->addAttributeGroup($entityTypeId, $attrSetId, $groupName, $groupSortOrder);
            }
        }
    }

    /**
     * Remove attribute sets and attribute groups if not exist
     *
     * @param array $data
     *
     * @return void
     */
    protected function _pruneAttributeSets(array $data)
    {
        $entityTypeId         = $this->_catalogProductEntityTypeId;
        $magAttributeSetsName = $this->_getAttributeSetsNameAsArray();
        $attributeSetsName    = [];

        foreach ($data as $attributeSet) {
            $attributeSetsName[] = $attributeSet['attribute_set_name'];
        }

        $attributeSetsToRemove = array_diff($magAttributeSetsName, $attributeSetsName);
        foreach ($attributeSetsToRemove as $attributeSet) {
            $this->_setup->removeAttributeSet($entityTypeId, $attributeSet);
        }

        foreach ($data as $attributeSet) {
            $attrSetName     = $attributeSet['attribute_set_name'];
            $attributeGroups = $attributeSet;
            unset($attributeGroups['attribute_set_name']);
            unset($attributeGroups['sortOrder']);

            $attrSetId = $this->_setup->getAttributeSet($entityTypeId, $attrSetName, 'attribute_set_id');

            $currentGroups = $this->_getAttributeGroups($attrSetId);

            $groupsToRemove = array_keys(array_diff_key($currentGroups, $attributeGroups));
            foreach ($groupsToRemove as $groupToRemoveName) {
                $this->_setup->removeAttributeGroup($entityTypeId, $attrSetId, $groupToRemoveName);
            }
        }
    }

    /**
     * Gives current attribute sets name as array
     * Returns ['name', ...]
     *
     * @return array
     */
    protected function _getAttributeSetsNameAsArray()
    {
        $attributeSetCollection = Mage::getModel('eav/entity_attribute_set')
            ->getCollection()
            ->setEntityTypeFilter($this->_catalogProductEntityTypeId);

        $attributeSetsName = [];
        foreach ($attributeSetCollection as $attrSet) {
            $attrSetAsArray      = $attrSet->getData();
            $attributeSetsName[] = $attrSetAsArray['attribute_set_name'];
        }

        return $attributeSetsName;
    }

    /**
     * Gives attribute groups which come from the given attribute set
     * Returns ['attribute group name' => 'sort order', ...]
     *
     * @param $attrSetId
     *
     * @return array
     */
    protected function _getAttributeGroups($attrSetId)
    {
        $connexion = $this->_setup->getConnection();
        $getOldGroupsQuery = $connexion
            ->select()
            ->from($this->_setup->getTable('eav/attribute_group'))
            ->where('attribute_set_id = :attribute_set_id');

        $bind = array('attribute_set_id' => $attrSetId);

        $currentGroups = [];
        foreach ($connexion->fetchAssoc($getOldGroupsQuery, $bind) as $attrGroup) {
            $currentGroups[$attrGroup['attribute_group_name']] = $attrGroup['sort_order'];
        }

        return $currentGroups;
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
