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

define('BENCHMARK',     true);
define('NUM_PRODUCTS',  10000);
define('BUNCH_NUM',     400);
define('USE_API',       true);
define('API_USER',      'apiUser');
define('API_KEY',       'someApiKey123');

if(BENCHMARK) {
    printf('Dropping old entities...' . PHP_EOL);
    Mage::getSingleton('core/resource')->getConnection('core_write')->query('TRUNCATE TABLE catalog_product_entity');
    
    printf('Generating %d testproducts...' . PHP_EOL, NUM_PRODUCTS);
    $bunches  = array();
    $products = array();
    for($i = 1; $i <= NUM_PRODUCTS; $i++) {
        $products[$i] = array(
           'sku'                => 'some_sku_' . $i,
           '_type'              => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
           '_attribute_set'     => 'Default',
            'name'              => 'Some product ( ' . $i . ' )',
            'price'             => rand(1, 1000),
            'description'       => 'Some description',
            'short_description' => 'Some short description',
            'weight'            => rand(1, 1000),
            'status'            => Mage_Catalog_Model_Product_Status::STATUS_ENABLED,
            'visibility'        => Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
            'tax_class_id'      => 0
        );
        
        if(($i && $i % BUNCH_NUM == 0) || $i == NUM_PRODUCTS) {
            $bunches[] = $products;
            $products = array();
        }
    }
    printf('Generated %d bunches of %d products each!' . PHP_EOL, count($bunches), BUNCH_NUM);
    
    printf('Importing products... Using %s!' . PHP_EOL, USE_API ? 'the external API' : 'Magento models');
    $totalTime = microtime(true);
    if(USE_API) {
        $client = new Zend_XmlRpc_Client(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'api/xmlrpc/');
        $client->getHttpClient()->setConfig(array(
            'timeout' => -1
        ));
        $session = $client->call('login', array(API_USER, API_KEY));
        
        $client->call('multiCall', array($session,
            array(
                array('import.setBehavior',       Mage_ImportExport_Model_Import::BEHAVIOR_APPEND),
                array('import.setEntityTypeCode', Mage_Catalog_Model_Product::ENTITY),
                array('import.importEntities',    array($bunches))
            )
        ));
        
        $client->call('endSession', array($session));
    } 
    else {
        $import = Mage::getModel('api_import/import');
        $import->getDataSourceModel()
            ->setBehavior(Mage_ImportExport_Model_Import::BEHAVIOR_APPEND)
            ->setEntityTypeCode(Mage_Catalog_Model_Product::ENTITY)
            ->setEntities($bunches);

        $import->importSource();
    }
    $totalTime = microtime(true) - $totalTime;

    printf(PHP_EOL . '========== Import statistics ==========' . PHP_EOL);
    printf("Total duration:\t\t%fs"    . PHP_EOL, $totalTime);
    printf("Average per product:\t%fs" . PHP_EOL, $totalTime / NUM_PRODUCTS);
    printf("Products per second:\t%fs" . PHP_EOL, 1 / ($totalTime / NUM_PRODUCTS));
    printf("Products per hour:\t%fs"   . PHP_EOL, (60 * 60) / ($totalTime / NUM_PRODUCTS));
}