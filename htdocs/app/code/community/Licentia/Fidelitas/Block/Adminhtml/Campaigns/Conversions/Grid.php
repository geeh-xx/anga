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
class Licentia_Fidelitas_Block_Adminhtml_Campaigns_Conversions_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('campaign_grid');
        $this->setDefaultSort('order_date');
        $this->setDefaultDir('DESC');
        $this->setEmptyText($this->__('No campaigns have been sent yet.'));
        $this->setUseAjax(true);
    }

    protected function _prepareCollection() {

        $current = Mage::registry('current_campaign');

        $collection = Mage::getModel('fidelitas/conversions')
                ->getResourceCollection();
        $children = Mage::getModel('fidelitas/campaigns')
                ->getChildrenCampaigns($current->getId())
                ->getAllIds();
        $children[] = $current->getId();
        $collection->addFieldToFilter('campaign_id', array('in' => $children));

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('conversion_id', array(
            'header' => $this->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'conversion_id',
        ));

        /*
          $this->addColumn('campaign_name', array(
          'header' => $this->__('Campaign Name'),
          'align' => 'left',
          'index' => 'campaign_name',
          ));
         */

        $this->addColumn('subscriber_email', array(
            'header' => $this->__('Email'),
            'align' => 'left',
            'index' => 'subscriber_email',
        ));


        $this->addColumn('subscriber_firstname', array(
            'header' => $this->__('First Name'),
            'align' => 'left',
            'index' => 'subscriber_firstname',
        ));


        $this->addColumn('subscriber_lastname', array(
            'header' => $this->__('Last Name'),
            'align' => 'left',
            'index' => 'subscriber_lastname',
        ));


        $this->addColumn('order_date', array(
            'header' => $this->__('Date'),
            'align' => 'left',
            'index' => 'order_date',
            'width' => '170px',
            'type' => 'datetime',
        ));


        $this->addColumn('order_amount', array(
            'header' => $this->__('Order Amount'),
            'type' => 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index' => 'order_amount',
        ));

        $this->addColumn('action', array(
            'header' => $this->__('Order'),
            'type' => 'action',
            'width' => '75px',
            'filter' => false,
            'align' => 'center',
            'sortable' => false,
            'actions' => array(array(
                    'url' => $this->getUrl('adminhtml/sales_order/view', array('order_id' => '$order_id')),
                    'caption' => $this->__('View'),
                )),
            'index' => 'type',
            'sortable' => false
        ));

        $this->addColumn('customer_id', array(
            'header' => $this->__('Customer'),
            'align' => 'center',
            'width' => '75px',
            'index' => 'customer_id',
            'filter' => false,
            'align' => 'center',
            'sortable' => false,
            'frame_callback' => array($this, 'customerResult'),
        ));


        $this->addColumn('subscriber_id', array(
            'header' => $this->__('Subscriber'),
            'align' => 'center',
            'filter' => false,
            'align' => 'center',
            'sortable' => false,
            'width' => '75px',
            'index' => 'subscriber_id',
            'frame_callback' => array($this, 'subscriberResult'),
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return false;
    }

    public function customerResult($value) {

        if ((int) $value > 0) {
            $url = $this->getUrl('/customer/edit', array('id' => $value));
            return'<a href="' . $url . '">' . $this->__('View') . '</a>';
        }

        return $this->__('No');
    }

    public function subscriberResult($value) {

        if ((int) $value > 0) {
            $url = $this->getUrl('/fidelias_subscriber/edit', array('id' => $value));
            return'<a href="' . $url . '">' . $this->__('View') . '</a>';
        }

        return $this->__('No');
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/gridconv', array('_current' => true));
    }

}