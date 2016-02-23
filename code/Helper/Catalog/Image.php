<?php

class Danslo_ApiImport_Helper_Catalog_Image extends Mage_Catalog_Helper_Image
{
    /**
     * Check - is this file an image
     *
     * @param string $filePath
     * @return bool
     * @throws Mage_Core_Exception
     */
    public function validateUploadFile($filePath) {
        if (!getimagesize($filePath)) {
            Mage::throwException($this->__('Disallowed file type.'));
        }

        $_processor = new Danslo_ApiImport_Model_Image($filePath);
        $hasMimeType = $_processor->getMimeType() !== null;
        unset($_processor);

        return $hasMimeType;
    }
}