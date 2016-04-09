<?php

class Danslo_ApiImport_Model_Import_Uploader extends Mage_ImportExport_Model_Import_Uploader
{
    /**
     * Initiate uploader default settings
     */
    public function init()
    {
        parent::init();

        if (version_compare(Mage::getVersion(), '1.9.2.0') >= 0) {
            $this->addValidateCallback(
                'catalog_product_image',
                Mage::helper('api_import/catalog_image'),
                'validateUploadFile'
            );
        }
    }
}