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
class Licentia_Fidelitas_Model_Segments_Condition_Activity extends Mage_Rule_Model_Condition_Abstract {

    public function loadAttributeOptions() {
        $attributes = array(
            'factivity_abandoned' => Mage::helper('fidelitas')->__('Days with an abandoned cart'),
            'factivity_anniversary' => Mage::helper('fidelitas')->__('Days to anniversary'),
            'factivity_age' => Mage::helper('fidelitas')->__('Customer Age'),
            'factivity_pending_payment' => Mage::helper('fidelitas')->__('Days with a pending payment for an order'),
            'factivity_last_order' => Mage::helper('fidelitas')->__('Days since last complete order'),
            'factivity_first_order' => Mage::helper('fidelitas')->__('Days since first complete order'),
            'factivity_last_review' => Mage::helper('fidelitas')->__('Days since last review'),
            'factivity_last_tag' => Mage::helper('fidelitas')->__('Days since last product tagged'),
            'factivity_account' => Mage::helper('fidelitas')->__('Days since account creation'),
            'factivity_number_reviews' => Mage::helper('fidelitas')->__('Number of Reviews'),
            'factivity_number_tags' => Mage::helper('fidelitas')->__('Number of Tags'),
            'factivity_number_orders' => Mage::helper('fidelitas')->__('Number of Completed Orders'),
            'factivity_percentage_complete_orders' => Mage::helper('fidelitas')->__('Percentage of Completed Orders'),
            'factivity_order_amount' => Mage::helper('fidelitas')->__('Lifetime Sales Amount'),
            'factivity_order_average' => Mage::helper('fidelitas')->__('Lifetime Sales Average'),
            'factivity_percentage_order_amount' => Mage::helper('fidelitas')->__('Percentage of Global Average Order Amount'),
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

        return 'numeric';
    }

    public function getValueElementType() {
        return 'text';
    }

    /**
     * Validate Address Rule Condition
     *
     * @param Varien_Object $object
     * @return bool
     */
    public function validate(Varien_Object $object) {

        $dbAttrName = str_replace('factivity_', '', $this->getAttribute());

        $list = Mage::registry('current_list');
        if ($list->getStoreId() != $object->getStoreId() && $object->getStoreId() > 0) {
            return false;
        }

        if (!in_array($dbAttrName, array('number_reviews', 'age', 'anniversary', 'order_average', 'number_tags', 'order_amount',
                    'percentage_order_amount', 'percentage_complete_orders', 'number_orders'))) {

            if (is_numeric($this->getValueParsed())) {
                $outputFormat = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_FULL);
                $date = new Zend_Date(null, $outputFormat);
                $dataTemp = $date->subDay($this->getValueParsed())->get();
                $data = date('Y-m-d', $dataTemp);
                $this->setValue($data);
            }

            if (is_array($this->getValueParsed())) {
                $extraArray = array();
                foreach ($this->getValueParsed() as $value) {
                    if (!is_numeric($this->getValueParsed())) {
                        continue;
                    }
                    $outputFormat = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_FULL);
                    $date = new Zend_Date(null, $outputFormat);
                    $data = $date->subDay($value)->get();
                    $extraArray[] = date('Y-m-d', $data);
                }

                $this->setValue($extraArray);
            }
        }

        $this->setValueParsed(null);

        if ($dbAttrName == 'abandoned') {

            $model = Mage::getResourceModel('sales/quote_collection')
                    ->addFieldToSelect('updated_at')
                    ->addFieldToFilter('customer_id', $object->getId())
                    ->addFieldToFilter('items_count', array('neq' => '0'))
                    ->addFieldToFilter('store_id', array('in' => $list->getStoreIdsArray()))
                    ->addFieldToFilter('is_active', '1');

            if ($this->translateOperator() == 'eq') {
                $model->addFieldToFilter('updated_at', array('from' => $this->getValueParsed(), 'to' => $this->getValueParsed()));
            } else {
                $model->addFieldToFilter('updated_at', array($this->translateOperator() => $this->getValueParsed()));
            }
        } elseif ($dbAttrName == 'age') {

            $now = Mage::app()->getLocale()->date()->get(Licentia_Fidelitas_Model_Campaigns::MYSQL_DATE);

            $model = Mage::getModel('customer/customer')
                    ->getCollection()
                    ->addAttributeToSelect('dob')
                    ->addAttributeToFilter('entity_id', $object->getId());

            $dob = $model->getFirstItem()->getData('dob');

            if (is_null($dob)) {
                return false;
            }

            $firstDay = new Zend_Date($dob, 'YYYY-MM-dd');
            $lastDay = new Zend_Date($now, 'YYYY-MM-dd');
            $diff = $lastDay->sub($firstDay);
            $years = ceil($diff->getTimestamp() / 60 / 60 / 24 / 365);

            if ($years < 0) {
                return false;
            }

            $object->setData($this->getAttribute(), $years);

            return parent::validate($object);
        } elseif ($dbAttrName == 'anniversary') {

            $now = Mage::app()->getLocale()->date()->get(Licentia_Fidelitas_Model_Campaigns::MYSQL_DATE);

            $model = Mage::getModel('customer/customer')
                    ->getCollection()
                    ->addAttributeToSelect('dob')
                    ->addAttributeToFilter('entity_id', $object->getId());

            $dob = $model->getFirstItem()->getData('dob');

            if (is_null($dob)) {
                return false;
            }

            $firstDay = new Zend_Date($dob, 'YYYY-MM-dd');
            $lastDay = new Zend_Date($now, 'YYYY-MM-dd');
            $firstDay->setYear($lastDay->get('yyyy'));
            $diff = $firstDay->sub($lastDay);
            $days = ceil($diff->getTimestamp() / 60 / 60 / 24);

            if ($days < 0) {
                return false;
            }

            $object->setData($this->getAttribute(), $days);

            return parent::validate($object);
        } elseif ($dbAttrName == 'pending_payment') {

            $model = Mage::getResourceModel('sales/order_collection')
                    ->addAttributeToSelect('created_at')
                    ->addAttributeToFilter('store_id', array('in' => $list->getStoreIdsArray()))
                    ->addAttributeToFilter('state', Mage_Sales_Model_Order::STATE_PENDING_PAYMENT)
                    ->addAttributeToFilter('customer_id', $object->getId());

            if ($this->translateOperator() == 'eq') {
                $model->addFieldToFilter('updated_at', array('from' => $this->getValueParsed(), 'to' => $this->getValueParsed()));
            } else {
                $model->addFieldToFilter('updated_at', array($this->translateOperator() => $this->getValueParsed()));
            }
        } elseif ($dbAttrName == 'last_order') {

            $model = Mage::getResourceModel('sales/order_collection')
                    ->addAttributeToSelect('created_at')
                    ->addAttributeToFilter('store_id', array('in' => $list->getStoreIdsArray()))
                    ->addAttributeToFilter('state', Mage_Sales_Model_Order::STATE_COMPLETE)
                    ->addAttributeToFilter('customer_id', $object->getId())
                    ->setOrder('created_at', 'ASC')
                    ->setPageSize(1);

            if ($this->translateOperator() == 'eq') {
                $model->addFieldToFilter('updated_at', array('from' => $this->getValueParsed(), 'to' => $this->getValueParsed()));
            } else {
                $model->addFieldToFilter('updated_at', array($this->translateOperator() => $this->getValueParsed()));
            }
        } elseif ($dbAttrName == 'first_order') {

            $model = Mage::getResourceModel('sales/order_collection')
                    ->addAttributeToSelect('created_at')
                    ->addAttributeToFilter('store_id', array('in' => $list->getStoreIdsArray()))
                    ->addAttributeToFilter('state', Mage_Sales_Model_Order::STATE_COMPLETE)
                    ->addAttributeToFilter('customer_id', $object->getId())
                    ->setOrder('created_at', 'DESC')
                    ->setPageSize(1);

            if ($this->translateOperator() == 'eq') {
                $model->addFieldToFilter('updated_at', array('from' => $this->getValueParsed(), 'to' => $this->getValueParsed()));
            } else {
                $model->addFieldToFilter('updated_at', array($this->translateOperator() => $this->getValueParsed()));
            }
        } elseif ($dbAttrName == 'last_review') {

            $model = Mage::getModel('review/review')->getCollection()
                    ->addFieldToFilter('customer_id', $object->getId())
                    ->addFieldToFilter('store_id', array('in' => $list->getStoreIdsArray()))
                    ->setOrder('created_at', 'DESC')
                    ->setPageSize(1);

            if ($this->translateOperator() == 'eq') {
                $model->addFieldToFilter('created_at', array('from' => $this->getValueParsed(), 'to' => $this->getValueParsed()));
            } else {
                $model->addFieldToFilter('created_at', array($this->translateOperator() => $this->getValueParsed()));
            }
        } elseif ($dbAttrName == 'last_tag') {

            $model = Mage::getModel('tag/tag')
                    ->getCustomerCollection()
                    ->addFieldToFilter('entity_id', $object->getId())
                    ->addFieldToFilter('store_id', array('in' => $list->getStoreIdsArray()))
                    ->setOrder('created_at', 'DESC')
                    ->setPageSize(1);

            if ($this->translateOperator() == 'eq') {
                $model->addFieldToFilter('updated_at', array('from' => $this->getValueParsed(), 'to' => $this->getValueParsed()));
            } else {
                $model->addFieldToFilter('updated_at', array($this->translateOperator() => $this->getValueParsed()));
            }
        } elseif ($dbAttrName == 'account') {

            $model = Mage::getModel('customer/customer')
                    ->getCollection()
                    ->addAttributeToSelect('updated_at')
                    ->addAttributeToFilter('customer_id', $object->getId())
                    ->addAttributeToFilter('store_id', array('in' => $list->getStoreIdsArray()));

            if ($this->translateOperator() == 'eq') {
                $model->addFieldToFilter('updated_at', array('from' => $this->getValueParsed(), 'to' => $this->getValueParsed()));
            } else {
                $model->addFieldToFilter('updated_at', array($this->translateOperator() => $this->getValueParsed()));
            }
        } elseif ($dbAttrName == 'number_reviews') {

            $model = Mage::getModel('review/review')->getCollection()
                    ->addFieldToFilter('customer_id', $object->getId())
                    ->addFieldToFilter('store_id', array('in' => $list->getStoreIdsArray()));

            $object->setData($this->getAttribute(), $model->count());

            return parent::validate($object);
        } elseif ($dbAttrName == 'number_tags') {

            $model = Mage::getModel('tag/tag')
                    ->getCustomerCollection()
                    ->addFieldToFilter('entity_id', $object->getId())
                    ->addFieldToFilter('store_id', array('in' => $list->getStoreIdsArray()))
            ;
            $object->setData($this->getAttribute(), $model->count());

            return parent::validate($object);
        } elseif ($dbAttrName == 'number_orders') {

            $model = Mage::getResourceModel('sales/order_collection')
                    ->addAttributeToSelect('created_at')
                    ->addAttributeToFilter('store_id', array('in' => $list->getStoreIdsArray()))
                    ->addAttributeToFilter('state', Mage_Sales_Model_Order::STATE_COMPLETE)
                    ->addAttributeToFilter('customer_id', $object->getId());

            $object->setData($this->getAttribute(), $model->count());

            return parent::validate($object);
        } elseif ($dbAttrName == 'percentage_complete_orders') {

            $model = Mage::getResourceModel('sales/order_collection')
                    ->addAttributeToSelect('created_at')
                    ->addAttributeToFilter('store_id', array('in' => $list->getStoreIdsArray()))
                    ->addAttributeToFilter('state', Mage_Sales_Model_Order::STATE_COMPLETE)
                    ->addAttributeToFilter('customer_id', $object->getId());

            $model1 = Mage::getResourceModel('sales/order_collection')
                    ->addAttributeToSelect('created_at')
                    ->addAttributeToFilter('store_id', array('in' => $list->getStoreIdsArray()))
                    ->addAttributeToFilter('state', array('neq' => Mage_Sales_Model_Order::STATE_COMPLETE))
                    ->addAttributeToFilter('customer_id', $object->getId());


            $total = ($model->count() + $model1->count());

            if ($total > 0) {
                $perc = round($model->count() * 100 / $total);
            } else {
                $perc = 0;
            }

            $object->setData($this->getAttribute(), $perc);

            return parent::validate($object);
        } elseif ($dbAttrName == 'percentage_order_amount') {

            if (!Mage::registry('fidelitas_sales_average')) {
                $collection = Mage::getResourceModel('reports/order_collection')
                        ->calculateSales(true);
                $collection->addAttributeToFilter('store_id', array('in' => $list->getStoreIdsArray()));

                $collection->load();
                $sales = $collection->getFirstItem();

                Mage::register('fidelitas_sales_average', $sales->getAverage());
            }

            $averageOrders = Mage::registry('fidelitas_sales_average');

            $customerTotals = Mage::getResourceModel('sales/sale_collection')
                    ->setOrderStateFilter(Mage_Sales_Model_Order::STATE_CANCELED, true)
                    ->setCustomerFilter($object)
                    ->load()
                    ->getTotals();

            $percAverage = round(100 * $customerTotals->getBaseAvgsale() / $averageOrders);

            $object->setData($this->getAttribute(), $percAverage);

            return parent::validate($object);
        } elseif ($dbAttrName == 'order_amount') {

            $customerTotals = Mage::getResourceModel('sales/sale_collection')
                    ->setOrderStateFilter(Mage_Sales_Model_Order::STATE_CANCELED, true)
                    ->setCustomerFilter($object)
                    ->load()
                    ->getTotals();

            $object->setData($this->getAttribute(), round($customerTotals->getBaseLifetime()));

            return parent::validate($object);
        } elseif ($dbAttrName == 'order_average') {

            $customerTotals = Mage::getResourceModel('sales/sale_collection')
                    ->setOrderStateFilter(Mage_Sales_Model_Order::STATE_CANCELED, true)
                    ->setCustomerFilter($object)
                    ->load()
                    ->getTotals();

            $object->setData($this->getAttribute(), round($customerTotals->getBaseAvgsale()));

            return parent::validate($object);
        }

        #Mage::log((string) $model->getSelect() . '-' . $dbAttrName, null, 'segments.txt', true);

        if ($model->count() > 0) {
            return true;
        } else {
            return false;
        }
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

        $newValue = array('==' => 'eq', '!=' => 'neq', '>=' => 'lteq', '<=' => 'gteq', '>' => 'lt', '<' => 'gt', '{}' => 'like', '!{}' => 'nlike', '()' => 'in', '!()' => 'nin');

        if (isset($newValue[$operator]))
            return $newValue[$operator];

        return 'eq';
    }

}
