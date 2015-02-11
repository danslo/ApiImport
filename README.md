## What are the main features of ApiImport?

1. The glaringly obvious one: being able to do imports programmatically rather than manually uploading a CSV file.
2. Importing bundled products.
3. Importing categories.
4. Importing attributes, attribute sets and attribute groups.
5. Associate attribute(s) with an attribute group in one or more attribute sets you specify.
6. Reindex products after they are imported.
7. Automatic creation of dropdown attribute values.
8. Useful events for enriching your entities from other Magento modules.

Additionally, ApiImport is 100% free from rewrites - making it upgrade-proof and reliable.

## How do I install ApiImport?

ApiImport is both [modman](https://github.com/colinmollenhour/modman) and [composer](https://getcomposer.org/download/) compatible.

### Install directly with modman

``./modman clone ApiImport https://github.com/danslo/ApiImport.git``

### Install through composer

Add something like the following to your ``composer.json``:

```json
{
    "require": {
        "danslo/api-import": "1.1.0"
    },
    "extra": {
        "magento-root-dir": "htdocs/"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/danslo/ApiImport.git"
        },
        {
            "type": "composer",
            "url": "http://packages.firegento.com"
        }
    ]
}
```

Afterwards, issue the ``composer install`` command.

## How do I use ApiImport?

### Access it directly through Magento models

```php
<?php

require_once 'app/Mage.php';

Mage::init('admin');

$api = Mage::getModel('api_import/import_api');
try {
    $api->importEntities($anArrayWithYourEntities, $entityType, $optionalImportBehavior);
} catch (Exception $e) {
    printf("%s: %s\n", $e->getMessage(), $e->getCustomMessage());
}
```
### Access it through the Magento Webservices API (any SOAP/XMLRPC capable language)

```php
<?php

require_once 'app/Mage.php';

Mage::init('admin');

// Get an XMLRPC client.
$client = new Zend_XmlRpc_Client(
    Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'api/xmlrpc/');

// For testing, just set an infinite timeout.
$client->getHttpClient()->setConfig(array('timeout' => -1));

// Login to the API and get a session token.
$session = $client->call('login', array($yourApiUser, $yourApiKey));

// Import regular entities (products, categories, customers).
$client->call('call', array(
    $session, 
    'import.importEntities', 
    array($anArrayWithYourEntities, $entityType, $optionalImportBehavior)
));

// Import attribute sets.
$client->call('call', array(
    $session,
    'import.importAttributeSets',
    array($anArrayWithYourAttributeSets, $optionalImportBehavior)
));

// Import attributes.
$client->call('call', array(
    $session, 
    'import.importAttributes', 
    array($anArrayWithYourAttributes, $optionalImportBehavior)
));

// Import attribute assocations.
$client->call('call', array(
    $session, 
    'import.importAttributeAssociations', 
    array($anArrayWithYourAssociations, $optionalImportBehavior)
));

// End our session.
$client->call('endSession', array($session));
```

## What kind of data does ApiImport expect?

### Entities

Because ApiImport is built as an extension to Magento's Import/Export functionality, it expects the exact same format! The only difference is that you are now supplying ApiImport with the data in an array format, rather than a CSV file. CSV column names are simply array keys, and CSV values are array values.

The ``Danslo_ApiImport_Helper_Test`` class provides several examples on how to programmatically generate your entities.

Another useful trick to figure out what to give to ApiImport is to simply use Magento to generate an exported CSV file of some sample entities.

For attributes, attribute sets and associate attributes to attribute groups in an attribute set, you can have a look to test.php, where you can find some example data. It's not the same than CSV because there is no way to import it with CSV in Magento.

### Entity types

The second parameter to importEntities specifies what kind of entity is imported. By default it will assume you are importing products. If you want to import a different kind of entity, use the return value of any of these methods:

1. ``Mage_ImportExport_Model_Import_Entity_Product::getEntityTypeCode()``
2. ``Mage_ImportExport_Model_Import_Entity_Customer::getEntityTypeCode()``
3. ``Danslo_ApiImport_Model_Import_Entity_Category::getEntityTypeCode()``

### Import behaviors

Magento will choose a replace behavior by default. If you would like to use another import behavior, you can pick one of these:

1. ``Mage_ImportExport_Model_Import::BEHAVIOR_APPEND`` - Tries to append images, related products, etc. instead of replacing them.
2. ``Mage_ImportExport_Model_Import::BEHAVIOR_REPLACE`` - Simply replaces the data in Magento with whatever you have in the entity array. Any data you do not specify in your array will not be deleted!
3. ``Mage_ImportExport_Model_Import::BEHAVIOR_DELETE`` - Deletes every product you have specified. You probably don't want to use this.
4. ``Danslo_ApiImport_Model_Import::BEHAVIOR_STOCK`` - Magento normally requires ``sku``, ``_type``, ``_attribute_set``. This is not useful when you simply want to update stock of existing entities. With this behavior you can simply specify ``sku`` and ``qty``!
5. ``Danslo_ApiImport_Model_Import::BEHAVIOR_DELETE_IF_NOT_EXIST`` - Works with attributes, attribute sets and attribute associations import. Any data you do not specify in your array will be deleted! Send your array, data that there are in Magento and not in your array are deleted.

## What if I only want to update stock?

Just import using the BEHAVIOR_STOCK behavior. See an example below:

```php
<?php

require_once 'app/Mage.php';

Mage::init('admin');

$api = Mage::getModel('api_import/import_api');
$api->importEntities(
    array(
        array(
            'sku' => 'some_sku',
            'qty' => 10
        ),
        array(
            'sku' => 'some_other_sku',
            'qty' => 20
        )
        // etc
    ),
    Mage_ImportExport_Model_Import_Entity_Product::getEntityTypeCode(),
    Danslo_ApiImport_Model_Import::BEHAVIOR_STOCK
);
```

Obviously you would generate your entities array programmatically.

## Where can I see the results?

As long as you have enabled developer mode (see index.php) and logging (see backend), ApiImport will write a log file every time it is run to:

``var/log/import_export/%Y/%m/%d/%time%_%operation_type%_%entity_type%.log``

There are plans to make this easily available through the backend.

## What Magento versions are supported by ApiImport?

ApiImport is intentionally only compatible with **Magento 1.6+**.

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

## License

Copyright 2014 Daniel Sloof

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
