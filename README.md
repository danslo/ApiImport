## What are the main features of ApiImport?

1. The glaringly obvious one: being able to do imports programmatically rather than manually uploading a CSV file.
2. Importing bundled products.
3. Importing categories.
4. Reindex products after they are imported.
5. Automatic creation of dropdown attribute values.
6. Useful events for enriching your entities from other Magento modules.

Additionally, ApiImport is 100% free from rewrites - making it upgrade-proof and reliable.

## How do I install ApiImport?

ApiImport is a [modman](https://github.com/colinmollenhour/modman) module. Simply install modman and execute the following command:

``./modman clone ApiImport https://github.com/danslo/ApiImport.git``

## How do I use ApiImport?

### Access it directly through Magento models

``` php
<?php

require_once 'app/Mage.php';

Mage::init();

$api = Mage::getModel('api_import/import_api');
$api->importEntities($anArrayWithYourEntities, $entityType);
```
### Access it through the Magento Webservices API (any SOAP/XMLRPC capable language)

``` php
<?php

require_once 'app/Mage.php';

Mage::init();

/*
 * Get an XmlRpc client.
 */
$client = new Zend_XmlRpc_Client(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'api/xmlrpc/');

/*
 * Set an infinite time-out.
 */
$client->getHttpClient()->setConfig(array('timeout' => -1));

/*
 * Login to webservices and obtain session.
 */
$session = $client->call('login', array($yourApiUser, $yourApiKey));

/*
 * Do your import.
 */
$client->call('call', array($session, 'import.importEntities', array($anArrayWithYourEntities, $entityType)));

/*
 * Clean up.
 */
$client->call('endSession', array($session));
```

## What kind of data does ApiImport expect?

### Entities

Because ApiImport is built as an extension to Magento's Import/Export functionality, it expects the exact same format! The only difference is that you are now supplying ApiImport with the data in an array format, rather than a CSV file. CSV column names are simply array keys, and CSV values are array values.

The ``Danslo_ApiImport_Helper_Test`` class provides several examples on how to programmatically generate your entities.

Another useful trick to figure out what to give to ApiImport is to simply use Magento to generate an exported CSV file of some sample entities.

### Entity types

The second parameter to importEntities specifies what kind of entity is imported. By default it will assume you are importing products. If you want to import a different kind of entity, use the return value of any of these methods:

1. ``Mage_ImportExport_Model_Export_Entity_Product::getEntityTypeCode()``
2. ``Mage_ImportExport_Model_Export_Entity_Customer::getEntityTypeCode()``
3. ``Danslo_ApiImport_Model_Export_Entity_Customer::getEntityTypeCode()``

## Where can I see the results?

As long as you have enabled developer mode (see index.php) and logging (see backend), ApiImport will write a log file every time it is run to:

``var/log/api_import_YYYY_MM_DD_hh_mm_ss.log``

There are plans to make this easily available through the backend.

## What Magento versions are supported by ApiImport?

ApiImport is intentionally only compatible with **Magento 1.6+**.

There were good reasons for not making it compatible with 1.5, but if you absolutely must:

1. Add the following config.xml node: config -> global -> models -> api_import_resource -> deprecatedNode = api_import_mysql4
2. Implement proxy resource classes with the old Mysql4 naming convention and then have them extend from the current resource models.

No bug reports will be considered for 1.5 installs.

## Benchmark

The following is a very simple benchmark run done on a Virtual Machine running Debian, with 1GB RAM and without any MySQL optimizations. Your experience may vary. These are fully indexed results, mind you.

    Generating 5000 simple products...
    Starting import...
    Done! Magento reports 5000 products in catalog.
    ========== Import statistics ==========
    Total duration:      37.475983s
    Average per product: 0.007495s
    Products per second: 133.418781s
    Products per hour:   480307.612782s
    =======================================

    Generating 5000 configurable products...
    Starting import...
    Done! Magento reports 10000 products in catalog.
    ========== Import statistics ==========
    Total duration:      68.099526s
    Average per product: 0.013620s
    Products per second: 73.421950s
    Products per hour:   264319.020648s
    =======================================

    Generating 5000 bundle products...
    Starting import...
    Done! Magento reports 15000 products in catalog.
    ========== Import statistics ==========
    Total duration:      113.453821s
    Average per product: 0.022691s
    Products per second: 44.070794s
    Products per hour:   158654.859310s
    =======================================

    Generating 5000 grouped products...
    Starting import...
    Done! Magento reports 20000 products in catalog.
    ========== Import statistics ==========
    Total duration:      62.553724s
    Average per product: 0.012511s
    Products per second: 79.931292s
    Products per hour:   287752.652192s
    =======================================