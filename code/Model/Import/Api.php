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

class Danslo_ApiImport_Model_Import_Api extends Mage_Api_Model_Resource_Abstract {

    protected $_api;

    public function __construct() {
        $this->_api = Mage::getSingleton('api_import/import');
    }

    public function importEntities($entities, $entityType = null, $behavior = null) {
        $this->_setEntityTypeCode($entityType ? $entityType : Mage_Catalog_Model_Product::ENTITY);
        $this->_setBehavior($behavior ? $behavior : Mage_ImportExport_Model_Import::BEHAVIOR_REPLACE);

	$this->_api->addLogComment(Mage::helper('importexport')->__('Start import'));

	/**
	 * Holder for all attributes
	 */
	$attributes = array ();

	/**
	 * Walk entities to lookup new attribute values for dropdown
	 */
	foreach ( $entities as $entity ) {

	    /**
	     * Walk its attributes
	     */
	    foreach ( $entity as $attributeName => $attributeValue ) {

		/**
		 * Load attribute and existing values only 1 time
		 */
		if ( !isset ($attributes[$attributeName]) ) {
		    // Load attribute
		    $attribute=Mage::getResourceModel('catalog/eav_attribute')->loadByCode(4, $attributeName);

		    // Only existing attributes will be checked, and if they are dropdown value
		    if ( $attribute->getId() && $attribute->getSourceModel()=='eav/entity_attribute_source_table' ) {
			$attributes[$attributeName]=array(
			    'attribute' => $attribute,
			    // Load values only one time, faster . . .
			    'values'	=> $attribute->getSource()->getAllOptions(false),
			    'changed'	=> false
			);
		    } else {
			/**
			 * Don't lookup twice
			 */
			$attributes[$attributeName]=false;
		    }
		}

		/**
		 * If attribute exists
		 */
		if ($attributes[$attributeName]) {

		    /**
		     * Look if value must be created
		     */
		    $attributeExists = false;
		    foreach ($attributes[$attributeName]['values']as$value) {
			if ( $value['label']==$attributeValue ) {
			    $attributeExists = true;
			}
		    }

		    /**
		     * attribute must be created
		     */
		    if (!$attributeExists) {

			/**
			 * Add value to lookup, it has to be added only once
			 */
			$attributes[$attributeName]['values'][] = array(
			    'value' => '0',
			    'label' => $attributeValue,
			);

			/**
			 * Read current new options
			 */
			$options = $attributes[$attributeName]['attribute']->getData('option');

			/**
			 * Set option count
			 */
			$optionCount = count($attributes[$attributeName]['values']);

			/**
			 * If there isn't one yet, initialize array
			 */
			if ( !is_array ( $options ) ) {
			    $options = array( 'value'=>array(), 'order'=>array() );
			}

			/**
			 * Add new option
			 */
			$options['value']['option_' . $optionCount]=array($attributeValue);
			$options['order']['option_' . $optionCount]=$optionCount;

			/**
			 * Update data in attribute
			 */
			$attributes[$attributeName]['attribute']->setData('option',$options);

			/**
			 * Identify change for one time save action
			 */
			$attributes[$attributeName]['changed']=true;
		    }
		}
	    }
	}

	/**
	 * Save changes in attributes
	 */
	foreach($attributes as $attribute) {
	    /**
	     * If attribute is a dropdown and changes are found
	     */
	    if ($attribute&&$attribute['changed']) {

		/**
		 * Write a log row
		 */
		$this->_api->addLogComment(Mage::helper('importexport')->__('New options saved for %s', $attribute['attribute']->getAttributeCode()));

		// Save
		$attribute['attribute']->save();
	    }
	}
	/**
	 * Clear mem, don't know if this is fully true, php has trouble with clearing memory when recursing
	 */
	unset($attributes);

        $this->_api->getDataSourceModel()->setEntities($entities);

	// Print all entities to the log
	//$this->_api->addLogComment(Mage::helper('importexport')->__('Entities: %s', print_r($entities,true)));

        try {
            $result = $this->_api->importSource();
        } catch(Mage_Core_Exception $e) {
            $this->_fault('import_failed', $e->getMessage());
        }

        return array($result);
    }

    protected function _setEntityTypeCode($entityType) {
        try {
            $this->_api->getDataSourceModel()->setEntityTypeCode($entityType);
        } catch(Mage_Core_Exception $e) {
            $this->_fault('invalid_entity_type', $e->getMessage());
        }
    }

    protected function _setBehavior($behavior) {
        try {
            $this->_api->getDataSourceModel()->setBehavior($behavior);
        } catch(Mage_Core_Exception $e) {
            $this->_fault('invalid_behavior', $e->getMessage());
        }
    }

}