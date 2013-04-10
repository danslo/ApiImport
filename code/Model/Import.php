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

class Danslo_ApiImport_Model_Import
    extends Mage_ImportExport_Model_Import
{

    /**
     * Overwrite XML path to entities so we can replace them with our own versions.
     */
    const CONFIG_KEY_ENTITIES = 'global/api_import/import_entities';

    /**
     * Log directory.
     */
    const LOG_DIRECTORY = 'log/import_export/';

    /**
     * Determines whether or not ApiImport writes import logs to var/log.
     *
     * @var boolean
     */
    protected $_debugMode = true;

    /**
     * Gets the data source model.
     *
     * @return Danslo_ApiImport_Model_Resource_Import_Data
     */
    public static function getDataSourceModel()
    {
        return Mage::getResourceSingleton('Danslo_ApiImport_Model_Resource_Import_Data');
    }

    /**
     * Imports entities from the source modle and logs the result.
     * This method will always return true because that's what core returns.
     *
     * @return boolean
     */
    public function importSource()
    {
        // Grab the entity type and import behavior from source model and store it.
        $this->setData(
            array(
                'entity'   => self::getDataSourceModel()->getEntityTypeCode(),
                'behavior' => self::getDataSourceModel()->getBehavior()
            )
        );
        $this->addLogComment(Mage::helper('Mage_ImportExport_Helper_Data')->__('Begin import of "%s" with "%s" behavior', $this->getEntity(), $this->getBehavior()));

        // Import entities and log the result.
        $result = $this->_getEntityAdapter()->importData();
        $this->addLogComment(
            array(
                Mage::helper('Mage_ImportExport_Helper_Data')->__(
                    'Checked rows: %d, checked entities: %d, invalid rows: %d, total errors: %d',
                    $this->getProcessedRowsCount(), $this->getProcessedEntitiesCount(),
                    $this->getInvalidRowsCount(), $this->getErrorsCount()
                ),
                Mage::helper('Mage_ImportExport_Helper_Data')->__('Import has been done successfuly.')
            )
        );

        // We circumvent validateSource, so we output the errors (if any) ourselves here.
        foreach ($this->getErrors() as $errorCode => $rows) {
            $this->addLogComment($errorCode . ' ' . Mage::helper('Mage_ImportExport_Helper_Data')->__('in rows') . ': ' . implode(', ', $rows));
        }
        return $result;
    }

    /**
     * The only reason for rewriting this method is so that the proper CONFIG_KEY_ENTITIES const value is used.
     * Nothing in this method has been changed.
     *
     * @throws Mage_Core_Exception
     * @return Mage_ImportExport_Model_Import_Entity_Abstract
     */
    protected function _getEntityAdapter()
    {
        if (!$this->_entityAdapter) {
            $validTypes = Mage_ImportExport_Model_Config::getModels(self::CONFIG_KEY_ENTITIES);

            if (isset($validTypes[$this->getEntity()])) {
                try {
                    $this->_entityAdapter = Mage::getModel($validTypes[$this->getEntity()]['model']);
                } catch (Exception $e) {
                    Mage::logException($e);
                    Mage::throwException(
                        Mage::helper('Mage_ImportExport_Helper_Data')->__('Invalid entity model: ' . $e->getMessage())
                    );
                }
                if (!($this->_entityAdapter instanceof Mage_ImportExport_Model_Import_Entity_Abstract ||
                      $this->_entityAdapter instanceof Mage_ImportExport_Model_Import_EntityAbstract)) {
                    Mage::throwException(
                        Mage::helper('Mage_ImportExport_Helper_Data')->__('Entity adapter object must be an instance of Mage_ImportExport_Model_Import_Entity_Abstract')
                    );
                }
            } else {
                Mage::throwException(Mage::helper('Mage_ImportExport_Helper_Data')->__('Invalid entity'));
            }
            if ($this->getEntity() != $this->_entityAdapter->getEntityTypeCode()) {
                Mage::throwException(
                    Mage::helper('Mage_ImportExport_Helper_Data')->__('Input entity code is not equal to entity adapter code')
                );
            }
            $this->_entityAdapter->setParameters($this->getData());
        }
        return $this->_entityAdapter;
    }

    /**
     * This fixes a core bug in at least 1.7.0.2 that references a constant of non-existent class.
     * This class is present in Enterprise, but has never existed in community.
     * The only changes are that we use a different LOG_DIRECTORY constant and remove some unused filename parts.
     *
     * @param mixed $debugData
     * @return Mage_ImportExport_Model_Abstract
     */
    public function addLogComment($debugData)
    {
        if (is_array($debugData)) {
            $this->_logTrace = array_merge($this->_logTrace, $debugData);
        } else {
            $this->_logTrace[] = $debugData;
        }
        if (!$this->_debugMode) {
            return $this;
        }

        if (!$this->_logInstance) {
            if (!$this->getRunAt()) {
                $this->setRunAt(date('H-i-s'));
            }
            $dirName  = date('Y' . DS .'m' . DS .'d' . DS);
            $fileName = join('_', array(
                $this->getRunAt(),
                $this->getBehavior(),
                $this->getEntity()
            ));
            $dirPath = Mage::getBaseDir('var') . DS . self::LOG_DIRECTORY . $dirName;
            if (!is_dir($dirPath)) {
                mkdir($dirPath, 0777, true);
            }
            $fileName = substr(strstr(self::LOG_DIRECTORY, DS), 1) . $dirName . $fileName . '.log';
            $this->_logInstance = Mage::getModel('Mage_Core_Model_Log_Adapter', $fileName)
                ->setFilterDataKeys($this->_debugReplacePrivateDataKeys);
        }
        $this->_logInstance->log($debugData);
        return $this;
    }

}
