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
class Licentia_Fidelitas_Model_Links extends Mage_Core_Model_Abstract {

    protected function _construct() {

        $this->_init('fidelitas/links');
    }

    public function getHashForCampaign($campaignId) {

        $return = $this->getCollection()
                ->addFieldToFilter('campaign_id', $campaignId)
                ->setOrder('link', 'asc')
        ;

        $info = array();

        foreach ($return as $item) {
            $info[$item->getId()] = $item->getLink();
        }

        return $info;
    }

    public function getLinksInCampaign($campaign) {
        $temp = array();
        $message = Mage::helper('cms')->getBlockTemplateProcessor()->filter($campaign->getMessage());

        $doc = new DOMDocument();
        $doc->loadHTML($message);

        foreach ($doc->getElementsByTagName('a') as $link) {
            $temp[] = $link->getAttribute('href');
        }
        
        return $temp;
    }

}
