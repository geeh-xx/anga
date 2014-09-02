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


class Licentia_Fidelitas_Block_Adminhtml_Reports_Detail extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();

        $this->_blockGroup = "fidelitas";
        $this->_controller = "adminhtml_subscribers";

        $this->_removeButton('add');
        $this->_removeButton('save');
        $this->_removeButton('reset');
        $this->_removeButton('delete');

        $url = $this->getUrl('*/fidelitas_campaigns/');
        $this->_updateButton('back', 'onclick', "setLocation('$url');");

    }

    public function getHeaderText() {

        $report = Mage::registry('current_report');

        return $this->__('Report Details. Last update: %s', Mage::helper('core')->formatDate($report->getUpdatedAt(), 'medium', true));
    }

}
