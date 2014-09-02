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
class Licentia_Fidelitas_Model_Conversions extends Mage_Core_Model_Abstract {

    protected function _construct() {

        $this->_init('fidelitas/conversions');
    }

    public function afterOrder($event) {
        $session = Mage::getSingleton('customer/session');

        if (!$session->getFidelitasConversion())
            return false;

        $campaign = Mage::getModel('fidelitas/campaigns')->load($session->getFidelitasConversionCampaign(), 'hash');
        $subscriber = Mage::getModel('fidelitas/subscribers')->load($session->getFidelitasConversionSubscriber(), 'uid');

        if (!$campaign->getId() || !$subscriber->getId()) {
            return false;
        }

        $order = $event->getEvent()->getOrder();

        $data = array();
        $data['campaign_id'] = $campaign->getId();
        $data['campaign_name'] = $campaign->getInternalName();
        $data['listnum'] = $campaign->getListnum();
        $data['subscriber_id'] = $subscriber->getId();
        $data['subscriber_email'] = $subscriber->getEmail();
        $data['subscriber_firstname'] = $subscriber->getFirstName();
        $data['subscriber_lastname'] = $subscriber->getLastName();
        $data['order_date'] = $order->getCreatedAt();
        $data['order_id'] = $order->getId();
        $data['order_amount'] = $order->getGrandTotal();
        $data['customer_id'] = $order->getCustomerId();

        $this->setData($data)->save();

        $campaign->setData('conversions_number', $campaign->getData('conversions_number') + 1);
        $campaign->setData('conversions_amount', $campaign->getData('conversions_amount') + $data['order_amount']);
        $campaign->setData('conversions_average', round($campaign->getData('conversions_amount') / $campaign->getData('conversions_number'), 2));
        $campaign->save();

        if ($campaign->getParentId()) {
            $parent = Mage::getModel('fidelitas/campaigns')->load($campaign->getParentId());

            if ($parent->getId()) {
                $parent->setData('conversions_number', $parent->getData('conversions_number') + 1);
                $parent->setData('conversions_amount', $parent->getData('conversions_amount') + $data['order_amount']);
                $parent->setData('conversions_average', round($parent->getData('conversions_amount') / $parent->getData('conversions_number'), 2));
                $parent->save();
            }
        }

        if ($campaign->getSplitId()) {
            if ($split = Mage::getModel('fidelitas/splits')->load($campaign->getSplitId())) {
                $split->setData('conversions_' . $campaign->getSplitVersion(), $split->getData('conversions_' . $campaign->getSplitVersion()) + 1);
                $split->save();
            }
        }

        $segments = explode(',', $campaign->getSegmentsIds());

        if (count($segments) > 0) {
            foreach ($segments as $segment) {

                $updateSegment = Mage::getModel('fidelitas/segments')->load($segment);

                if ($updateSegment->getId()) {
                    $updateSegment->setData('conversions_number', $updateSegment->getData('conversions_number') + 1);
                    $updateSegment->setData('conversions_amount', $updateSegment->getData('conversions_amount') + $data['order_amount']);
                    $updateSegment->setData('conversions_average', round($updateSegment->getData('conversions_amount') / $updateSegment->getData('conversions_number'), 2));
                    $updateSegment->save();

                    $consegment = Mage::getModel('fidelitas/consegments');
                    $data['segment_id'] = $segment;
                    $consegment->addData($data);
                    $consegment->save();
                }
            }
        }

        $subscriber->setData('conversions_number', $subscriber->getData('conversions_number') + 1);
        $subscriber->setData('conversions_amount', $subscriber->getData('conversions_amount') + $data['order_amount']);
        $subscriber->setData('conversions_average', round($subscriber->getData('conversions_amount') / $subscriber->getData('conversions_number'), 2));
        $subscriber->save();


        #$session->setFidelitasConversion(false);
        #$session->setFidelitasConversionCampaign(false);
        #$session->setFidelitasConversionSubscriber(false);

        return true;
    }

    public function startConversion($event) {
        return true;
        $request = $event->getControllerAction()->getRequest();
        $uid = $request->getParam('uid');
        $camp = $request->getParam('fidcamp');

        $url = base64_decode($request->getParam('url'));

        Mage::register('fidelitas_open_url', $url);

        if (!$camp && !$uid)
            return false;

        $session = Mage::getSingleton('customer/session');
        $session->setFidelitasConversion(true);
        $session->setFidelitasConversionCampaign($camp);
        $session->setFidelitasConversionSubscriber($uid);

        $campaign = Mage::getModel('fidelitas/campaigns')->load($camp, 'hash');
        $subscriber = Mage::getModel('fidelitas/subscribers')->load($uid, 'uid');

        Mage::getModel('fidelitas/stats')->logClicks($campaign, $subscriber);

        $request->setParam('uid', null);
        $request->setParam('fidcamp', null);


        return true;
    }

}
