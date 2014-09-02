<?php

/**
 * Licentia Fidelitas - Advanced Email and SMS Marketing Automation for E-Goi
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Creative Commons Attribution-NonCommercial 4.0 International  
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc/4.0/
 *
 * @title      Advanced Email and SMS Marketing Automation
 * @category   Marketing
 * @package    Licentia
 * @author     Bento Vilas Boas <bento@licentia.pt>
 * @copyright  Copyright (c) 2012 Licentia - http://licentia.pt
 * @license    Creative Commons Attribution-NonCommercial 4.0 International 
 */
class Licentia_Fidelitas_Model_Products_List extends Mage_Core_Model_Abstract {

    public function toOptionArray() {
        return array(
            array('value' => 'attributes', 'label' => Mage::helper('fidelitas')->__('Products Attributes')),
            array('value' => 'related_order', 'label' => Mage::helper('fidelitas')->__('Related Products From Last Completed Order')),
            array('value' => 'related', 'label' => Mage::helper('fidelitas')->__('Related Products From Previous Completed Orders')),
            array('value' => 'abandoned', 'label' => Mage::helper('fidelitas')->__('Products In Abandoned Cart')),
            array('value' => 'categories', 'label' => Mage::helper('fidelitas')->__('Categories Views')),
            array('value' => 'wishlist', 'label' => Mage::helper('fidelitas')->__('Wishlist Items')),
            array('value' => 'views', 'label' => Mage::helper('fidelitas')->__('Product Views')),
            array('value' => 'recent', 'label' => Mage::helper('fidelitas')->__('Recent Added')),
        );
    }

}
