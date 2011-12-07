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

class Danslo_ApiImport_Model_Import_Entity_Product_Type_Bundle
    extends Mage_ImportExport_Model_Import_Entity_Product_Type_Abstract {

    /**
     * Column names that hold values with particular meaning.
     *
     * @var array
     */
    protected $_particularAttributes = array(
        '_bundle_option_required', '_bundle_option_position',
        '_bundle_option_type', '_bundle_option_title', '_bundle_option_store',
        '_bundle_product_sku', '_bundle_product_position', '_bundle_product_is_default',
        '_bundle_product_price_type', '_bundle_product_price_value', '_bundle_product_qty',
        '_bundle_product_can_change_qty'
    );

    /**
     * Allowed types for bundle options.
     *
     * @var array
     */
    protected $_bundleOptionTypes = array('select', 'radio', 'checkbox', 'multi');

    /*
     * If no type is selected, fall back to a default dropdown.
     */
    const DEFAULT_OPTION_TYPE = 'select';

    public function saveData() {
        $connection         = $this->_entityModel->getConnection();
        $newSku             = $this->_entityModel->getNewSku();
        $oldSku             = $this->_entityModel->getOldSku();
        $optionTable        = Mage::getSingleton('core/resource')->getTableName('bundle/option');
        $optionValueTable   = Mage::getSingleton('core/resource')->getTableName('bundle/option_value');
        $selectionTable     = Mage::getSingleton('core/resource')->getTableName('bundle/selection');
        $productData        = null;
        $productId          = null;

        while ($bunch = $this->_entityModel->getNextBunch()) {
            $bundleOptions    = array();
            $bundleSelections = array();

            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->_entityModel->isRowAllowedToImport($rowData, $rowNum)) {
                    continue;
                }
                $scope = $this->_entityModel->getRowScope($rowData);
                if (Mage_ImportExport_Model_Import_Entity_Product::SCOPE_DEFAULT == $scope) {
                    $productData = $newSku[$rowData[Mage_ImportExport_Model_Import_Entity_Product::COL_SKU]];

                    if ($this->_type != $productData['type_id']) {
                        $productData = null;
                        continue;
                    }
                    $productId = $productData['entity_id'];
                } elseif (null === $productData) {
                    continue;
                }

                if(empty($rowData['_bundle_option_title'])) {
                    continue;
                }
                if(isset($rowData['_bundle_option_type']) && strlen($rowData['_bundle_option_type'])) {
                    if(!in_array($rowData['_bundle_option_type'], $this->_bundleOptionTypes)) {
                        continue;
                    }

                    $bundleOptions[$productId][$rowData['_bundle_option_title']] = array(
                        'parent_id' => $productId,
                        'required'  => !empty($rowData['_bundle_option_required']) ? $rowData['_bundle_option_required'] : '0',
                        'position'  => !empty($rowData['_bundle_option_position']) ? $rowData['_bundle_option_position'] : '0',
                        'type'      => !empty($rowData['_bundle_option_type'])     ? $rowData['_bundle_option_type']     : self::DEFAULT_OPTION_TYPE
                    );
                }
                if(isset($rowData['_bundle_product_sku']) && strlen($rowData['_bundle_product_sku'])) {
                    $selectionEntityId = false;
                    if (isset($newSku[$rowData['_bundle_product_sku']])) {
                        $selectionEntityId = $newSku[$rowData['_bundle_product_sku']]['entity_id'];
                    } elseif (isset($oldSku[$rowData['_bundle_product_sku']])) {
                        $selectionEntityId = $oldSku[$rowData['_bundle_product_sku']]['entity_id'];
                    }

                    if($selectionEntityId) {
                        $bundleSelections[$productId][$rowData['_bundle_option_title']][] = array(
                            'parent_product_id'         => $productId,
                            'product_id'                => $selectionEntityId,
                            'position'                  => !empty($rowData['_bundle_product_position'])       ? $rowData['_bundle_product_position']        : '0',
                            'is_default'                => !empty($rowData['_bundle_product_is_default'])     ? $rowData['_bundle_product_is_default']      : '0',
                            'selection_price_type'      => !empty($rowData['_bundle_product_price_type'])     ? $rowData['_bundle_product_price_type']      : '0',
                            'selection_price_value'     => !empty($rowData['_bundle_product_price_value'])    ? $rowData['_bundle_product_price_value']     : '0',
                            'selection_qty'             => !empty($rowData['_bundle_product_qty'])            ? $rowData['_bundle_product_qty']             : '1',
                            'selection_can_change_qty'  => !empty($rowData['_bundle_product_can_change_qty']) ? $rowData['_bundle_product_can_change_qty']  : '1'
                        );
                    }
                }
            }

            if(count($bundleOptions)) {
                if($this->_entityModel->getBehavior() != Mage_ImportExport_Model_Import::BEHAVIOR_APPEND) {
                    $quoted = $connection->quoteInto('IN (?)', array_keys($bundleOptions));
                    $connection->delete($optionTable, "parent_id {$quoted}");
                    $connection->delete($selectionTable, "parent_product_id {$quoted}");
                }

                /*
                 * Insert options.
                 */
                $optionData = array();
                foreach($bundleOptions as $productId => $options) {
                    foreach($options as $title => $option) {
                        $optionData[] = $option;
                    }
                }
                $connection->insertOnDuplicate($optionTable, $optionData);

                /*
                 * Insert option titles.
                 */
                $optionId = $connection->lastInsertId();
                $optionValues = array();
                foreach($bundleOptions as $productId => $options) {
                    foreach($options as $title => $option) {
                        $optionValues[] = array(
                            'option_id' => $optionId++,
                            'store_id'  => '0',
                            'title'     => $title
                        );
                    }
                }
                $connection->insertOnDuplicate($optionValueTable, $optionValues);
                $optionId -= count($optionData);

                if(count($bundleSelections)) {
                    /*
                     * Insert option selections.
                     */
                    $optionSelections = array();
                    foreach($bundleSelections as $productId => $selections) {
                        foreach($selections as $title => $selection) {
                            foreach($selection as &$sel) {
                                $sel['option_id'] = $optionId;
                            }
                            $optionId++;
                            $optionSelections = array_merge($optionSelections, $selection);
                        }
                    }
                    $connection->insertOnDuplicate($selectionTable, $optionSelections);
                }
            }
        }

        return $this;
    }

}
