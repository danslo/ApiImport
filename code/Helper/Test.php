<?php

class Danslo_ApiImport_Helper_Test {
    
    protected $_defaultAttributes = array(
        'description'       => 'Some description',
        '_attribute_set'    => 'Default',
        'short_description' => 'Some short description',
        'status'            => Mage_Catalog_Model_Product_Status::STATUS_ENABLED,
        'visibility'        => Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
        'tax_class_id'      => 0
    );
    
    public function removeAllProducts() {
        Mage::getSingleton('core/resource')->getConnection('core_write')->query('TRUNCATE TABLE catalog_product_entity');
    }
    
    public function generateRandomSimpleProducts($numProducts, $bunchNum) {
        $bunches  = array();
        $products = array();
        for($i = 1; $i <= $numProducts; $i++) {
            $products[$i] = array_merge($this->_defaultAttributes, array(
               'sku'                => 'some_sku_' . $i,
               '_type'              => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                'name'              => 'Some product ( ' . $i . ' )',
                'price'             => rand(1, 1000),
                'weight'            => rand(1, 1000)
            ));

            if(($i && $i % $bunchNum == 0) || $i == $numProducts) {
                $bunches[] = $products;
                $products = array();
            }
        }
        return $bunches;
    }
    
    public function generateRandomConfigurableProducts($numProducts, $bunchNum) {
        $bunches  = array();
        $products = array();
        
        /*
         * Create a bunch of simples that we can associate.
         * Obviously in a 'real' import, most of these will be unique simples.
         */
        $simples = current($this->generateRandomSimpleProducts(3, 3));
        foreach(array('red', 'yellow', 'green') as $key => $color) {
            $simples[$key + 1]['color'] = $color;
            $counter++;
        }
        $bunches[] = $simples;

        for($i = 1, $counter = 4; $i <= $numProducts; $i++) {
            /*
             * Create a configurable.
             */
            $configurable = array_merge($this->_defaultAttributes, array(
               'sku'                    => 'some_configurable_' . $i,
                '_type'                 => Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
                'name'                  => 'Some configurable ( ' . $i . ' )',
                'price'                 => rand(1, 1000),
                'weight'                => rand(1, 1000)
            ));
            $products[$counter] = $configurable;
            
            /*
             * Now associate all the simple products.
             */
            foreach($simples as $simple) {
                $products[$counter]['_super_products_sku']     = $simple['sku'];
                $products[$counter]['_super_attribute_code']   = 'color';
                $products[$counter]['_super_attribute_option'] = $simple['color'];
                $counter++;
            }
            
            if(($i && $i % $bunchNum == 0) || $i == $numProducts) {
                $bunches[] = $products;
                $products = array();
            }
        }

        return $bunches;
    }
    
}