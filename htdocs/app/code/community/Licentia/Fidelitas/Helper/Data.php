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
class Licentia_Fidelitas_Helper_Data extends Mage_Core_Helper_Abstract {

    const URL_VERSION = 'http://apps.licentia.pt/fidelitas/latest';
    const URL_NEWS = 'http://apps.licentia.pt/fidelitas/news';

    public function getCurrentVersion() {
        return (string) Mage::getConfig()->getNode()->modules->Licentia_Fidelitas->version;
    }

    public function getExtensionNews() {
        return Mage::helper('core')->escapeHtml(file_get_contents(self::URL_NEWS), array('h4', 'p', 'ul', 'li', 'strong', 'br'));
    }

    public function getLastestVersion() {
        return Mage::helper('core')->escapeHtml(file_get_contents(self::URL_VERSION));
    }

    public function isCustomerSubscribed($customerId) {
        $col = Mage::getModel('fidelitas/subscribers')
                ->getCollection()
                ->addFieldToFilter('customer_id', $customerId)
                ->setPageSize(1);

        if ($col->count() == 1) {
            return $col->getFirstItem();
        }

        return false;
    }

    public function canSendSms($type) {
        $status = Mage::getStoreConfig('fidelitas/comments/sms_in_' . strtolower($type));

        if (!$status)
            return false;

        $info = Mage::registry('current_' . $type);

        if ($info) {
            if ($type == 'order') {
                $billing = $info->getBillingAddress();
            } else {
                $billing = $info->getOrder()->getBillingAddress();
            }
            $prefix = Mage::getModel('fidelitas/subscribers')->getPrefixForCountry($billing->getCountryId());

            $number = preg_replace('/\D/', '', $billing->getTelephone());
            $number = ltrim($number, $prefix);
            $number = ltrim($number, 0);

            return $prefix . '-' . $number;
        }
    }

}
