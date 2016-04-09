<?php

class Danslo_ApiImport_Model_Image extends Varien_Image
{
    /**
     * Destruct
     */
    public function __destruct()
    {
        $adpater = $this->_getAdapter();

        if ($adpater instanceof Varien_Image_Adapter_Gd2) {
            $adpater->destruct();
        }
    }
}