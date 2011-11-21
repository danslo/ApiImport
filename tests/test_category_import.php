<?php

require_once 'app/Mage.php';

Mage::init();

$api = Mage::getModel('api_import/import_api');
$api->importEntities(array(
    array(
        'something' => 'else',
        'thingsome' => 'more' 
    )),
    'catalog_category');
