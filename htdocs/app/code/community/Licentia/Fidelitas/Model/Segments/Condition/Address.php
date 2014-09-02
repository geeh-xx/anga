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
class Licentia_Fidelitas_Model_Segments_Condition_Address extends Mage_Rule_Model_Condition_Abstract {

    public function loadAttributeOptions() {
        $attributes = array(
            'faddress_base_subtotal' => Mage::helper('fidelitas')->__('Subtotal'),
            'faddress_total_qty_ordered' => Mage::helper('fidelitas')->__('Total Items Quantity'),
            'faddress_weight' => Mage::helper('fidelitas')->__('Total Weight'),
            'faddress_payment_method' => Mage::helper('fidelitas')->__('Payment Method'),
            'faddress_shipping_method' => Mage::helper('fidelitas')->__('Shipping Method'),
            'faddress_postcode' => Mage::helper('fidelitas')->__('Shipping Postcode'),
            'faddress_region' => Mage::helper('fidelitas')->__('Shipping Region'),
            'faddress_region_id' => Mage::helper('fidelitas')->__('Shipping State/Province'),
            'faddress_country_id' => Mage::helper('fidelitas')->__('Shipping Country'),
            'faddress_created_at' => Mage::helper('fidelitas')->__('Ordered Date'),
        );

        $this->setAttributeOption($attributes);

        return $this;
    }

    public function getAttributeElement() {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    public function getInputType() {
        switch ($this->getAttribute()) {
            case 'faddress_base_subtotal': case 'faddress_weight': case 'faddress_total_qty_ordered':
                return 'numeric';

            case 'faddress_shipping_method': case 'faddress_payment_method': case 'faddress_country_id': case 'faddress_region_id':
                return 'select';
            case 'faddress_created_at':
                return 'date';
        }
        return 'string';
    }

    public function getValueElementType() {
        switch ($this->getAttribute()) {
            case 'faddress_shipping_method': case 'faddress_payment_method': case 'faddress_country_id': case 'faddress_region_id':
                return 'select';
            case 'faddress_created_at':
                return 'date';
        }
        return 'text';
    }

    public function getValueSelectOptions() {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'faddress_country_id':
                    $options = Mage::getModel('adminhtml/system_config_source_country')
                            ->toOptionArray();
                    break;

                case 'faddress_region_id':
                    $options = Mage::getModel('adminhtml/system_config_source_allregion')
                            ->toOptionArray();
                    break;

                case 'faddress_shipping_method':
                    $options = Mage::getModel('adminhtml/system_config_source_shipping_allmethods')
                            ->toOptionArray();
                    break;

                case 'faddress_payment_method':
                    $options = Mage::getModel('adminhtml/system_config_source_payment_allmethods')
                            ->toOptionArray();
                    break;

                default:
                    $options = array();
            }
            $this->setData('value_select_options', $options);
        }
        return $this->getData('value_select_options');
    }

    /**
     * Validate Address Rule Condition
     *
     * @param Varien_Object $object
     * @return bool
     */
    public function validate(Varien_Object $object) {

        $list = Mage::registry('current_list');


        $dbAttrName = str_replace('faddress_', '', $this->getAttribute());

        if (in_array($dbAttrName, array('shipping_method', 'base_subtotal', 'created_at', 'total_qty_ordered', 'weight'))) {

            $model = Mage::getModel('sales/order')
                    ->getCollection()
                    ->addAttributeToSelect($dbAttrName)
                    ->addAttributeToFilter('store_id', array('in' => $list->getStoreIdsArray()))
                    ->addAttributeToFilter('state', Mage_Sales_Model_Order::STATE_COMPLETE)
                    ->addAttributeToFilter($dbAttrName, array($this->translateOperator() => $this->getValueParsed()));

            //BACKWARDS COMPATABILITY
            $model->getSelect()->where('customer_id=? OR customer_email=?', $object->getId(), $object->getEmail());
        } elseif (in_array($dbAttrName, array('payment_method'))) {

            $orders = Mage::getModel('sales/order')
                    ->getCollection()
                    ->addAttributeToSelect('entity_id')
                    ->addAttributeToFilter('state', Mage_Sales_Model_Order::STATE_COMPLETE)
                    ->addAttributeToFilter('customer_id', $object->getId());

            $ordersIds = array();
            foreach ($orders as $order) {
                $ordersIds[] = $order->getId();
            }
            $model = Mage::getModel('sales/order_payment')
                    ->getCollection()
                    ->addAttributeToSelect('method')
                    ->addAttributeToFilter('parent_id', array('in' => $ordersIds))
                    ->addAttributeToFilter('method', $this->getValueParsed());
        } else {

            $model = Mage::getModel('sales/order_address')
                    ->getCollection()
                    ->addAttributeToSelect($dbAttrName)
                    ->addAttributeToFilter('customer_id', $object->getId())
                    ->addAttributeToFilter($dbAttrName, array($this->translateOperator() => $this->getValueParsed()));
        }


        if ($model->count() == 0) {
            $object->setData($this->getAttribute(), '_-|----');
            return false;
        } else {
            return true;
            $object->setData($this->getAttribute(), $this->getValueParsed());
        }

        return parent::validate($object);
    }

    public function collectValidatedAttributes($customerCollection) {

        $attribute = $this->getAttribute();

        $attributes = $this->getRule()->getCollectedAttributes();
        $attributes[$attribute] = true;
        $this->getRule()->setCollectedAttributes($attributes);

        return $this;
    }

    public function translateOperator() {

        $operator = $this->getOperator();

        $newValue = array('==' => 'eq', '!=' => 'neq', '>=' => 'gteq', '<=' => 'lteq', '>' => 'gt', '<' => 'lt', '{}' => 'like', '!{}' => 'nlike', '()' => 'in', '!()' => 'nin');

        if (isset($newValue[$operator]))
            return $newValue[$operator];

        return 'eq';
    }

}
