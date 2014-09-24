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

class Danslo_ApiImport_Model_Resource_Import_Data
    implements IteratorAggregate
{

    /**
     * Stores all data passed to us by the user.
     *
     * @var array
     */
    protected $_entities = array();

    /**
     * Stores all data in bunches (configurable number).
     *
     * @var array
     */
    protected $_bunches = array();

    /**
     * Stores the entity type.
     *
     * @var string
     */
    protected $_entityTypeCode = null;

    /**
     * Stores the import behavior.
     *
     * @var string
     */
    protected $_behavior = null;

    /**
     * Array iterator for bunches.
     *
     * @var ArrayIterator
     */
    protected $_iterator = null;

    /**
     * Optionally creates and returns an iterator for bunches.
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        if ($this->_iterator === null) {
            $this->_generateBunches();
            if (empty($this->_bunches)) {
                Mage::throwException('Import resource model was not provided any entities.');
            }
            $this->_iterator = new ArrayIterator($this->_bunches);
        }

        return $this->_iterator;
    }

    /**
     * Splits up entities array by bunches.
     *
     * @return void
     */
    public function _generateBunches()
    {
        $this->_bunches = array();
        $products = array();
        $bunchNum = Mage::getStoreConfig('api_import/import_settings/bunch_num');
        $i = 1;
        foreach ($this->_entities as $product) {
            $products[$i] = $product;
            if (($i && $i % $bunchNum == 0) || $i == count($this->_entities)) {
                $this->_bunches[] = $products;
                $products = array();
            }
            $i++;
        }
    }

    /**
     * Stores entities and resets iterator.
     *
     * @param  array                                        $entities
     * @return \Danslo_ApiImport_Model_Resource_Import_Data
     */
    public function setEntities($entities)
    {
        if (count($entities)) {
            $this->_entities = $entities;
            $this->_iterator = null;
        }

        return $this;
    }

    /**
     * Returns entities.
     *
     * @return array
     */
    public function getEntities()
    {
        return $this->_entities;
    }

    /**
     * Returns the entity type.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        if ($this->_entityTypeCode === null) {
            Mage::throwException('Import resource model was not provided any entity type.');
        }

        return $this->_entityTypeCode;
    }

    /**
     * Returns the import behavior.
     *
     * @return string
     */
    public function getBehavior()
    {
        if ($this->_behavior === null) {
            Mage::throwException('Import resource model was not provided any import behavior.');
        }

        return $this->_behavior;
    }

    /**
     * Validates and sets the import behavior.
     *
     * @param  string                                      $behavior
     * @return Danslo_ApiImport_Model_Resource_Import_Data
     */
    public function setBehavior($behavior)
    {
        $allowedBehaviors = array(
            Mage_ImportExport_Model_Import::BEHAVIOR_APPEND,
            Mage_ImportExport_Model_Import::BEHAVIOR_REPLACE,
            Mage_ImportExport_Model_Import::BEHAVIOR_DELETE,
            Danslo_ApiImport_Model_Import::BEHAVIOR_STOCK
        );
        if (!in_array($behavior, $allowedBehaviors)) {
            Mage::throwException('Specified import behavior (%s) is not in allowed behaviors: %s', $behavior, implode(', ', $allowedBehaviors));
        }
        $this->_behavior = $behavior;

        return $this;
    }

    /**
     * Valdiates and sets the entity type.
     *
     * @param  string                                      $entityTypeCode
     * @return Danslo_ApiImport_Model_Resource_Import_Data
     */
    public function setEntityTypeCode($entityTypeCode)
    {
        $allowedEntities = array_keys(Mage_ImportExport_Model_Config::getModels(Danslo_ApiImport_Model_Import::CONFIG_KEY_ENTITIES));
        if (!in_array($entityTypeCode, $allowedEntities)) {
            Mage::throwException('Specified entity type (%s) is not in allowed entity types: %s', $entityTypeCode, implode(', ', $allowedEntities));
        }
        $this->_entityTypeCode = $entityTypeCode;

        return $this;
    }

    /**
     * Returns the next bunch in line.
     *
     * @return array
     */
    public function getNextBunch()
    {
        if ($this->_iterator === null) {
            $this->_iterator = $this->getIterator();
            $this->_iterator->rewind();
        }
        if ($this->_iterator->valid()) {
            $dataRow = $this->_iterator->current();
            $this->_iterator->next();
        } else {
            $this->_iterator = null;
            $dataRow = null;
        }

        return $dataRow;
    }

}
