<?php
/*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class AdminImportController extends AdminImportControllerCore
{            
    public function __construct()
    {
        parent::__construct();
        
        switch ((int)Tools::getValue('entity')) {
            case $this->entities[$this->l('Products')]:
                self::$validators['image'] = array(
                    'AdminImportController',
                    'split'
                );

                $this->available_fields = array(
                    'no' => array('label' => $this->l('Ignore this column')),
                    'id' => array('label' => $this->l('ID')),
                    'active' => array('label' => $this->l('Active (0/1)')),
                    'name' => array('label' => $this->l('Name')),
                    'extern_link' => array('label' => $this->l('Extern Link')),
                    'category' => array('label' => $this->l('Categories (x,y,z...)')),
                    'price_tex' => array('label' => $this->l('Price tax excluded')),
                    'price_tin' => array('label' => $this->l('Price tax included')),
                    'id_tax_rules_group' => array('label' => $this->l('Tax rules ID')),
                    'wholesale_price' => array('label' => $this->l('Wholesale price')),
                    'on_sale' => array('label' => $this->l('On sale (0/1)')),
                    'reduction_price' => array('label' => $this->l('Discount amount')),
                    'reduction_percent' => array('label' => $this->l('Discount percent')),
                    'reduction_from' => array('label' => $this->l('Discount from (yyyy-mm-dd)')),
                    'reduction_to' => array('label' => $this->l('Discount to (yyyy-mm-dd)')),
                    'reference' => array('label' => $this->l('Reference #')),
                    'supplier_reference' => array('label' => $this->l('Supplier reference #')),
                    'supplier' => array('label' => $this->l('Supplier')),
                    'manufacturer' => array('label' => $this->l('Manufacturer')),
                    'ean13' => array('label' => $this->l('EAN13')),
                    'upc' => array('label' => $this->l('UPC')),
                    'ecotax' => array('label' => $this->l('Ecotax')),
                    'width' => array('label' => $this->l('Width')),
                    'height' => array('label' => $this->l('Height')),
                    'depth' => array('label' => $this->l('Depth')),
                    'weight' => array('label' => $this->l('Weight')),
                    'quantity' => array('label' => $this->l('Quantity')),
                    'minimal_quantity' => array('label' => $this->l('Minimal quantity')),
                    'visibility' => array('label' => $this->l('Visibility')),
                    'additional_shipping_cost' => array('label' => $this->l('Additional shipping cost')),
                    'unity' => array('label' => $this->l('Unit for the unit price')),
                    'unit_price' => array('label' => $this->l('Unit price')),
                    'description_short' => array('label' => $this->l('Short description')),
                    'description' => array('label' => $this->l('Description')),
                    'tags' => array('label' => $this->l('Tags (x,y,z...)')),
                    'meta_title' => array('label' => $this->l('Meta title')),
                    'meta_keywords' => array('label' => $this->l('Meta keywords')),
                    'meta_description' => array('label' => $this->l('Meta description')),
                    'link_rewrite' => array('label' => $this->l('URL rewritten')),
                    'available_now' => array('label' => $this->l('Text when in stock')),
                    'available_later' => array('label' => $this->l('Text when backorder allowed')),
                    'available_for_order' => array('label' => $this->l('Available for order (0 = No, 1 = Yes)')),
                    'available_date' => array('label' => $this->l('Product availability date')),
                    'date_add' => array('label' => $this->l('Product creation date')),
                    'show_price' => array('label' => $this->l('Show price (0 = No, 1 = Yes)')),
                    'image' => array('label' => $this->l('Image URLs (x,y,z...)')),
                    'delete_existing_images' => array(
                        'label' => $this->l('Delete existing images (0 = No, 1 = Yes)')
                    ),
                    'features' => array('label' => $this->l('Feature (Name:Value:Position:Customized)')),
                    'online_only' => array('label' => $this->l('Available online only (0 = No, 1 = Yes)')),
                    'condition' => array('label' => $this->l('Condition')),
                    'customizable' => array('label' => $this->l('Customizable (0 = No, 1 = Yes)')),
                    'uploadable_files' => array('label' => $this->l('Uploadable files (0 = No, 1 = Yes)')),
                    'text_fields' => array('label' => $this->l('Text fields (0 = No, 1 = Yes)')),
                    'out_of_stock' => array('label' => $this->l('Action when out of stock')),
                    'shop' => array(
                        'label' => $this->l('ID / Name of shop'),
                        'help' => $this->l('Ignore this field if you don\'t use the Multistore tool. If you leave this field empty, the default shop will be used.'),
                    ),
                    'advanced_stock_management' => array(
                        'label' => $this->l('Advanced Stock Management'),
                        'help' => $this->l('Enable Advanced Stock Management on product (0 = No, 1 = Yes).')
                    ),
                    'depends_on_stock' => array(
                        'label' => $this->l('Depends on stock'),
                        'help' => $this->l('0 = Use quantity set in product, 1 = Use quantity from warehouse.')
                    ),
                    'warehouse' => array(
                        'label' => $this->l('Warehouse'),
                        'help' => $this->l('ID of the warehouse to set as storage.')
                    ),
                );

                self::$default_values = array(
                    'id_category' => array((int)Configuration::get('PS_HOME_CATEGORY')),
                    'id_category_default' => null,
                    'active' => '1',
                    'width' => 0.000000,
                    'height' => 0.000000,
                    'depth' => 0.000000,
                    'weight' => 0.000000,
                    'visibility' => 'both',
                    'additional_shipping_cost' => 0.00,
                    'unit_price' => 0,
                    'quantity' => 0,
                    'minimal_quantity' => 1,
                    'price' => 0,
                    'id_tax_rules_group' => 0,
                    'description_short' => array((int)Configuration::get('PS_LANG_DEFAULT') => ''),
                    'link_rewrite' => array((int)Configuration::get('PS_LANG_DEFAULT') => ''),
                    'online_only' => 0,
                    'condition' => 'new',
                    'available_date' => date('Y-m-d'),
                    'date_add' => date('Y-m-d H:i:s'),
                    'date_upd' => date('Y-m-d H:i:s'),
                    'customizable' => 0,
                    'uploadable_files' => 0,
                    'text_fields' => 0,
                    'advanced_stock_management' => 0,
                    'depends_on_stock' => 0,
                );
            break;
        }        
    }       
}
