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

class Danslo_ApiImport_Model_Import_Entity_Product_Type_Grouped
    extends Mage_ImportExport_Model_Import_Entity_Product_Type_Grouped
{

    /*
     * Bugfix for core.
     * Magento uses a seperate getBehavior implementation rather than getting
     * the behavior directly from the entityModel.
     *
     * Unfortunately this does break importing grouped products when not done through
     * ApiImport. But there is no other way.
     */
    public function getBehavior() {
        return $this->_entityModel->getBehavior();
    }

}
