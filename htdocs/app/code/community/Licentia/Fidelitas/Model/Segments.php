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
class Licentia_Fidelitas_Model_Segments extends Mage_Rule_Model_Rule {

    protected $_eventPrefix = 'fidelitas_segments';
    protected $_eventObject = 'rule';
    protected $_customersIds;

    protected function _construct() {

        $this->_init('fidelitas/segments');
    }

    public function getOptionArray() {

        $lists = Mage::getModel('fidelitas/segments')
                ->getCollection()
                ->addFieldToSelect('segment_id')
                ->addFieldToSelect('name')
                ->addFieldToFilter('is_active', 1);

        $return = array();
        $return[] = array('value' => '0', 'label' => Mage::helper('fidelitas')->__("-- None --"));

        foreach ($lists as $list) {
            $return[] = array('value' => $list->getId(), 'label' => $list->getName());
        }

        return $return;
    }

    public function getConditionsInstance() {
        return Mage::getModel('fidelitas/segments_condition_combine');
    }

    public function getActionsInstance() {
        return Mage::getModel('fidelitas/segments_action_collection');
    }

    /**
     * Get array of product ids which are matched by rule
     *
     * @return array
     */
    public function getMatchingCustomersIds($customerId = false) {


        if (is_null($this->_customersIds)) {

            $this->_customersIds = array();
            $this->setCollectedAttributes(array());

            $customerCollection = Mage::getResourceModel('customer/customer_collection');
            $list = Mage::registry('current_list');

            if (!$customerId) {
                $subscribers = Mage::getModel('fidelitas/subscribers')
                        ->getCollection()
                        ->addFieldToSelect('customer_id')
                        ->addFieldToFilter('status', 1)
                        ->addFieldToFilter('list', $list->getListnum())
                        ->addFieldToFilter('customer_id', array('gt' => 0));

                $subs = array();
                foreach ($subscribers as $sub) {
                    $subs[] = $sub->getCustomerId();
                }

                $customerCollection->addAttributeToFilter('entity_id', array('in' => $subs));
            } else {
                $customerCollection->addAttributeToFilter('entity_id', $customerId);
            }

            $this->getConditions()->collectValidatedAttributes($customerCollection);

            Mage::getSingleton('core/resource_iterator')->walk(
                    $customerCollection->getSelect(), array(array($this, 'callbackValidateCustomer')), array(
                'attributes' => $this->getCollectedAttributes(),
                'customer' => Mage::getModel('customer/customer'),
                    )
            );
        }

        if (!$customerId) {
            Mage::getModel('fidelitas/evolutions')->log($this->getId(), $this->_customersIds);
        }
        return $this->_customersIds;
    }

    /**
     * Callback function for product matching
     *
     * @param $args
     * @return void
     */
    public function callbackValidateCustomer($args) {
        $customer = clone $args['customer'];
        $customer->setData($args['row']);

        if ($this->getConditions()->validate($customer)) {
            $this->_customersIds[] = $customer->getId();
        }
    }

    /**
     * Get array of assigned customer group ids
     *
     * @return array
     */
    public function getCustomerGroupIds() {
        $ids = $this->getData('customer_group_ids');
        if (($ids && !$this->getCustomerGroupChecked()) || is_string($ids)) {
            if (is_string($ids)) {
                $ids = explode(',', $ids);
            }

            $groupIds = Mage::getModel('customer/group')->getCollection()->getAllIds();
            $ids = array_intersect($ids, $groupIds);
            $this->setData('customer_group_ids', $ids);
            $this->setCustomerGroupChecked(true);
        }
        return $ids;
    }

    /**
     * Returns a list of segments IDS and internal name
     * @return type
     */
    public function toFormValues() {
        $return = array();
        $collection = $this->getCollection()
                ->addFieldToSelect('segment_id')
                ->addFieldToSelect('name');

        foreach ($collection as $segment) {
            $return[$segment->getId()] = $segment->getName();
        }

        return $return;
    }

    public function buildUser() {
        $segments = $this->getCollection()
                ->addFieldToFilter('build', 1);

        foreach ($segments as $segment) {
            Mage::getModel('fidelitas/segments')->load($segment->getId())->setData('build', 2)->save();
            Mage::getModel('fidelitas/segments_list')->loadList($segment->getId());
            Mage::getModel('fidelitas/segments')->load($segment->getId())->setData('build', 0)->save();
        }
    }

    public function cron() {

        $date = Mage::app()->getLocale()->date()->get(Licentia_Fidelitas_Model_Campaigns::MYSQL_DATE);

        $segments = $this->getCollection()
                ->addFieldToFilter('cron', array('neq' => '0'));

        //Version Compatability
        $segments->getSelect()
                ->where(" cron_last_run <? or cron_last_run IS NULL ", $date);


        foreach ($segments as $segment) {

            if ($segment->getCron() == 'd' && $segment->getChangeGroup() != 'd') {
                Mage::getModel('fidelitas/segments_list')->loadList($segment->getId());
                Mage::getModel('fidelitas/segments')->load($segment->getId())
                        ->setData('cron_last_run', $date)
                        ->setData('last_update', $date)
                        ->save();
            }

            if ($segment->getCron() == 'w' && $segment->getChangeGroup() != 'w' && $date->get('e') == 1) {
                Mage::getModel('fidelitas/segments_list')->loadList($segment->getId());
                Mage::getModel('fidelitas/segments')->load($segment->getId())
                        ->setData('cron_last_run', $date)
                        ->setData('last_update', $date)
                        ->save();
            }

            if ($segment->getCron() == 'm' && $segment->getChangeGroup() != 'm' && $date->get('d') == 1) {
                Mage::getModel('fidelitas/segments_list')->loadList($segment->getId());
                Mage::getModel('fidelitas/segments')->load($segment->getId())
                        ->setData('cron_last_run', $date)
                        ->setData('last_update', $date)
                        ->save();
            }
        }
    }

    public function _beforeSave() {

        if (!$this->getData('controller')) {
            return;
        }
        return parent::_beforeSave();
    }

}
