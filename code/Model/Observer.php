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

class Danslo_ApiImport_Model_Observer
{

    /**
     * Returns a connection.
     *
     * @return Varien_Db_Adapter_Pdo_Mysql
     */
    protected function _getConnection()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    /**
     * Index products.
     *
     * @param Varien_Event_Observer $observer
     * @return Danslo_ApiImport_Model_Observer
     */
    public function indexProducts($observer)
    {
        // Obtain all imported entity IDs.
        $entities   = $observer->getEntities();
        $connection = $this->_getConnection();
        $table      = $connection->getTableName('api_import_index_entity');
        $indexers   = Mage::helper('api_import/index')->getIndexers();

        // Store entity IDs in database.
        foreach ($indexers as $indexType => $indexConfig) {
            if (Mage::getStoreConfig(sprintf('api_import/import_settings/enable_%s_index', $indexConfig))) {
                // Determine data to be stored.
                $indexData = array();
                foreach ($entities as $entity) {
                    $indexData[$entity['entity_id']] = array(
                        'entity_id'  => $entity['entity_id'],
                        'index_type' => $indexType
                    );
                }

                // Delete old data first.
                $connection->delete($table, $connection->quoteInto(array(
                    'entity_id IN(?)' => array_keys($indexData),
                    'index_type = ?'  => $indexType
                )));

                // Insert new data.
                $connection->insertMultiple($table, $indexData);
            }
        }
        return $this;
    }

}
