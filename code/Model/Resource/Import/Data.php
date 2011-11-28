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

class Danslo_ApiImport_Model_Resource_Import_Data implements IteratorAggregate {

    protected $_entities         = array();
    protected $_bunches          = array();
    protected $_entityTypeCode   = null;
    protected $_behavior         = null;
    protected $_iterator         = null;

    public function getIterator() {
        if($this->_iterator === null) {
            $this->_generateBunches();
            if(empty($this->_bunches)) {
                Mage::throwException('Import resource model was not provided any entities.');
            }
            $this->_iterator = new ArrayIterator($this->_bunches);
        }
        return $this->_iterator;
    }

    public function _generateBunches() {
        /*
         * Split up entities by bunches.
         */
        $products = array();
        $bunchNum = Mage::getStoreConfig('api_import/import_settings/bunch_num');
        $i = 1;
        foreach($this->_entities as $product) {
            $products[$i] = $product;
            if(($i && $i % $bunchNum == 0) || $i == count($this->_entities)) {
                $this->_bunches[] = $products;
                $products = array();
            }
            $i++;
        }
    }

    public function setEntities($entities) {
        if(count($entities)) {
            $this->_entities = $entities;
            $this->_iterator = null;
        }
        return $this;
    }

    public function getEntities() {
        return $this->_entities;
    }

    public function getEntityTypeCode() {
        if($this->_entityTypeCode === null) {
            Mage::throwException('Import resource model was not provided any entity type.');
        }
        return $this->_entityTypeCode;
    }

    public function getBehavior() {
        if($this->_behavior === null) {
            Mage::throwException('Import resource model was not provided any import behavior.');
        }
        return $this->_behavior;
    }

    public function setBehavior($behavior) {
        $allowedBehaviors = array(
            Mage_ImportExport_Model_Import::BEHAVIOR_APPEND,
            Mage_ImportExport_Model_Import::BEHAVIOR_REPLACE,
            Mage_ImportExport_Model_Import::BEHAVIOR_DELETE);
        if(!in_array($behavior, $allowedBehaviors)) {
            Mage::throwException('Specified import behavior (%s) is not in allowed behaviors: %s', $behavior, implode(', ', $allowedBehaviors));
        }
        $this->_behavior = $behavior;
        return $this;
    }

    public function setEntityTypeCode($entityTypeCode) {
        $allowedEntities = array_keys(Mage_ImportExport_Model_Config::getModels(Danslo_ApiImport_Model_Import::CONFIG_KEY_ENTITIES));
        if(!in_array($entityTypeCode, $allowedEntities)) {
            Mage::throwException('Specified entity type (%s) is not in allowed entity types: %s', $entityTypeCode, implode(', ', $allowedEntities));
        }
        $this->_entityTypeCode = $entityTypeCode;
        return $this;
    }

    public function getNextBunch() {
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