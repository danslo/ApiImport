<?php
/**
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

require_once 'app/Mage.php';

Mage::init();

define('NUM_ENTITIES', 5000);
define('NUM_ROWS_BY_CALL', false);
define('API_USER', 'apiUser');
define('API_KEY', 'someApiKey123');
define('USE_API', true);
ini_set('memory_limit', '2048M');

$helper = Mage::helper('api_import/test');

if (USE_API) {
    // Create an API connection
    $soapOptions = array(
        'encoding'   => 'UTF-8',
        'trace'      => true,
        'exceptions' => true,
        'login'      => API_USER,
        'password'   => API_KEY,
        'cache_wsdl' => 3,
        'keep_alive' => 1
    );

    try{
        $client = new SoapClient(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'index.php/api/soap/?wsdl', $soapOptions);
        $session = $client->login(API_USER, API_KEY);
    } catch (Exception $e) {
        echo 'Exception :' . $e->getMessage();
    }
}

$entityTypes = array(
    'product' => array(
        'entity' => Mage_ImportExport_Model_Export_Entity_Product::getEntityTypeCode(),
        'model'  => 'catalog/product',
        'types'  => array(
            'simple',
            'configurable',
            'bundle',
            'grouped',
            'image',
            'localizable'
        ),
        'behavior' => 'append'
    ),
    'customer' => array(
        'entity' => Mage_ImportExport_Model_Export_Entity_Customer::getEntityTypeCode(),
        'model'  => 'customer/customer',
        'types'  => array(
            'standard'
        ),
        'behavior' => 'append'
    ),
    'category' => array(
        'entity' => Danslo_ApiImport_Model_Import_Entity_Category::getEntityTypeCode(),
        'model'  => 'catalog/category',
        'types'  => array(
            'standard'
        ),
        'behavior' => 'append'
    )
);

foreach ($entityTypes as $typeName => $entityType) {
    foreach ($entityType['types'] as $subType) {
        // Generation method depends on product type.
        printf('Generating %d %s %ss...' . PHP_EOL, NUM_ENTITIES, $subType, $typeName);
        $entities = $helper->{sprintf('generateRandom%s%s', ucfirst($subType), ucfirst($typeName))}(NUM_ENTITIES);

        // Attempt to import generated products.
        printf('Starting import...' . PHP_EOL);
        $totalTime = microtime(true);

        if (USE_API) {
            $data = array();

            if (count($entities) <= NUM_ROWS_BY_CALL || !NUM_ROWS_BY_CALL) {
                $data[] = $entities;
            } else {
                $data = array_chunk($entities, NUM_ROWS_BY_CALL);
            }

            try {
                foreach($data as $bulk) {
                    $client->call($session, 'import.importEntities', array($bulk, $entityType['entity'], $entityType['behavior']));
                }
            }
            catch(Exception $e) {
                printf('Import failed: ' . PHP_EOL, $e->getMessage());
                printf('Server returned: %s' . PHP_EOL, $client->getHttpClient()->getLastResponse()->getBody());
                exit;
            }
        } else {
            // For debugging purposes only.
            Mage::getModel('api_import/import_api')->importEntities($entities, $entityType['entity']);
        }
        printf('Done! Magento reports %d %ss.' . PHP_EOL, Mage::getModel($entityType['model'])->getCollection()->count(), $typeName);
        $totalTime = microtime(true) - $totalTime;

        // Generate some rough statistics.
        printf('========== Import statistics ==========' . PHP_EOL);
        printf("Total duration:\t\t%fs"    . PHP_EOL, $totalTime);
        printf("Average per %s:\t%fs" . PHP_EOL, $typeName, $totalTime / NUM_ENTITIES);
        printf("%ss per second:\t%fs" . PHP_EOL, ucfirst($typeName), 1 / ($totalTime / NUM_ENTITIES));
        printf("%ss per hour:\t%fs"   . PHP_EOL, ucfirst($typeName), (60 * 60) / ($totalTime / NUM_ENTITIES));
        printf('=======================================' . PHP_EOL . PHP_EOL);
    }
}

// Cleanup.
if (USE_API) {
    $client->endSession($session);
}
