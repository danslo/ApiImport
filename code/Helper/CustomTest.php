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

class ApiTest
{

    /**
     * Stores array of imported simple products used for configurable, bundle and grouped product creation.
     *
     * @var array
     */
    protected $_linkedProducts = null;

    /**
     * Default attributes that are used for every product entity.
     *
     * @var array
     */
    protected $_defaultProductAttributes = [
        '_attribute_set'    => 'Default',
        'short_description' => 'Some short description',
        '_product_websites' => 'base',
        'status'            => 1,
        'visibility'        => 4,
        'tax_class_id'      => 0,
        'is_in_stock'       => 1
    ];

    protected $_defaultStoreView = [
        'default',
        'fr_fr'
    ];

    /**
     * Default attributes that are used for customers.
     *
     * @var array
     */
    protected $_defaultCustomerAttributes = [
        '_website'          => 'base',
        '_store'            => 'default',
        'group_id'          => 1
    ];

    /**
     * Default attributes that are used for categories. 
     *
     * @var array
     */
    protected $_defaultCategoryAttributes = [
        '_root'             => 'Default Category',
        'is_active'         => 'yes',
        'include_in_menu'   => 'yes',
        'description'       => 'Category description',
        'meta_description'  => 'Category meta description',
        'available_sort_by' => 'position',
        'default_sort_by'   => 'position'
    ];

    /**
     * Creates and stores 3 simple products with different values for the color attribute.
     * These products are used for configurable, bundle and grouped product generation.
     *
     * @return array
     */
    protected function _getLinkedProducts()
    {
        // We create 3 simple products so we can test configurable/bundle links.
        if ($this->_linkedProducts === null) {
            $this->_linkedProducts = $this->generateRandomSimpleProduct(3);

            // Use the color option for configurables. Note that this attribute must be added to the specified attribute set!
            foreach (['red', 'yellow', 'green'] as $key => $color) {
                $this->_linkedProducts[$key + 1]['color'] = $color;
            }
            //Mage::getModel('api_import/import_api')->importEntities($this->_linkedProducts);
        }
        return $this->_linkedProducts;
    }

    /**
     * Generates random simple products with media.
     *
     * @param int $numProducts
     * @return array
     */
    public function generateRandomSimpleProduct($numProducts)
    {
        $products = [];

        for ($i = 1; $i <= $numProducts; $i++) {
            $products[$i] = array_merge(
                $this->_defaultProductAttributes,
                [
                    'sku'                => 'some_sku_' . $i,
                    '_type'              => 'simple',
                    'description'        => 'Description of the product ' . $i,
                    'name'               => 'Some product ( ' . $i . ' )',
                    'price'              => rand(1, 1000),
                    'weight'             => rand(1, 1000),
                    'qty'                => rand(1, 30),
                    'image'              => '/example_image.png',
                    'image_content'      => base64_encode(file_get_contents('example_image.png')),
                    '_media_image'       => '/example_image.png',
                    '_media_is_disabled' => '0'
                ]
            );
        }

        return $products;
    }


    /**
     * Generates random localizable simple products.
     *
     * @param int $numProducts
     * @return array
     */
    public function generateRandomLocalizableProduct($numProducts)
    {
        $products = [];
        $j = 0;

        for ($i = 1; $i <= $numProducts; $i++) {

            foreach ($this->_defaultStoreView as $locale) {
                if ('default' === $locale) {
                    $products[$j++] = array_merge(
                        $this->_defaultProductAttributes,
                        [
                            'sku'              => 'some_sku_' . $i,
                            '_type'            => 'simple',
                            'description'      => 'Some description ' . $locale,
                            'name'             => 'Some attr cust2 product ( ' . $i . ' ) ; Locale : ' . $locale,
                            'price'            => rand(1, 1000),
                            'weight'           => rand(1, 1000),
                            'qty'              => rand(1, 30),
                            'test_custom_attr' => 'my custom attribute ' . $locale,
                        ]
                    );
                } else {
                    $products[$j++] = [
                        '_store'            => $locale,
                        'short_description' => 'Courte description ' . $locale,
                        'description'       => 'Description ' . $locale,
                        'name'              => 'cust2 attr produit ( ' . $i . ' ) ; Locale : ' . $locale,
                        'test_custom_attr'  => 'Mon attribut custom ' . $locale
                    ];
                }
            }
        }

        return $products;
    }

    /**
     * Generates random configurable products.
     *
     * @param int $numProducts
     * @return array
     */
    public function generateRandomConfigurableProduct($numProducts)
    {
        $products = [];

        for ($i = 1, $counter = 1; $i <= $numProducts; $i++) {
            // Generate configurable product.
            $products[$counter] = array_merge(
                $this->_defaultProductAttributes,
                [
                    'sku'    => 'some_configurable_' . $i,
                    '_type'  => 'configurable',
                    'name'   => 'Some configurable ( ' . $i . ' )',
                    'price'  => rand(1, 1000),
                    'weight' => rand(1, 1000)
                ]
            );

            // Associate child products.
            foreach ($this->_getLinkedProducts() as $linkedProduct) {
                $products[$counter] = array_merge(
                    (isset($products[$counter]) ? $products[$counter] : []),
                    [
                        '_super_products_sku'     => $linkedProduct['sku'],
                        '_super_attribute_code'   => 'color',
                        '_super_attribute_option' => $linkedProduct['color']
                    ]
                );
                $counter++;
            }
        }

        return $products;
    }

    /**
     * Generates random bundle products.
     *
     * @param int $numProducts
     * @return array
     */
    public function generateRandomBundleProduct($numProducts)
    {
        $products = [];

        for ($i = 1, $counter = 1; $i <= $numProducts; $i++) {
            // Generate bundle product.
            $products[$counter] = array_merge(
                $this->_defaultProductAttributes, [
                    'sku'        => 'some_bundle_' . $i,
                    '_type'      => 'bundle',
                    'name'       => 'Some bundle ( ' . $i . ' )',
                    'price'      => rand(1, 1000),
                    'weight'     => rand(1, 1000),
                    'price_view' => 'price range',
                    'price_type' => 1
                ]
            );

            // Create an option.
            $optionTitle = 'Select a bundle item!';
            $products[$counter]['_bundle_option_title'] = $optionTitle;
            $products[$counter]['_bundle_option_type']  = 'select';

            // Associate option selections.
            foreach ($this->_getLinkedProducts() as $linkedProduct) {
                $products[$counter] = array_merge(
                    (isset($products[$counter]) ? $products[$counter] : []),
                    [
                        '_bundle_option_title'        => $optionTitle,
                        '_bundle_product_sku'         => $linkedProduct['sku'],
                        '_bundle_product_price_value' => rand(1, 1000)
                    ]
                );
                $counter++;
            }
        }

        return $products;
    }

    /**
     * Generates random grouped products.
     *
     * @param int $numProducts
     * @return array
     */
    public function generateRandomGroupedProduct($numProducts)
    {
        $products = [];

        // Generate grouped product.
        for ($i = 1, $counter = 1; $i <= $numProducts; $i++) {
            $products[$counter] = array_merge(
                $this->_defaultProductAttributes,
                [
                    'sku'   => 'some_grouped_' . $i,
                    '_type' => 'grouped',
                    'name'  => 'Some grouped ( ' . $i . ' )'
                ]
            );

            // Associated child products.
            foreach ($this->_getLinkedProducts() as $linkedProduct) {
                $products[$counter] = array_merge(
                    (isset($products[$counter]) ? $products[$counter] : []),
                    [
                        '_associated_sku'         => $linkedProduct['sku'],
                        '_associated_default_qty' => '1', // optional
                        '_associated_position'    => '0'  // optional
                    ]
                );
                $counter++;
            }
        }

        return $products;
    }

    /**
     * Generates random customers.
     *
     * @param int $numCustomers
     * @return array
     */
    public function generateRandomStandardCustomer($numCustomers)
    {
        $customers = [];

        for ($i = 0; $i < $numCustomers; $i++) {
            $customers[$i] = array_merge(
                $this->_defaultCustomerAttributes,
                [
                    'email'     => sprintf('%s@%s.com', uniqid(), uniqid()),
                    'firstname' => uniqid(),
                    'lastname'  => uniqid()
                ]
            );
        }

        return $customers;
    }

    /**
     * Generates random categories.
     *
     * @param int $numCategories
     * @return array
     */
    public function generateRandomStandardCategory($numCategories)
    {   
        $categories = [];
        
        for ($i = 1; $i <= $numCategories; $i++) {
            $categories[$i - 1] = array_merge(
                $this->_defaultCategoryAttributes,
                [
                    'name'          => sprintf('Test Category %d', $i),
                    '_category'     => sprintf('Test Category %d', $i),
                    'url_key'       => sprintf('test%d', $i),
                ]
            );
        } 

        return $categories;
    }

}
