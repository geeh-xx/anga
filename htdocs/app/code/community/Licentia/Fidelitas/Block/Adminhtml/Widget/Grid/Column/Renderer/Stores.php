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
class Licentia_Fidelitas_Block_Adminhtml_Widget_Grid_Column_Renderer_Stores extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $data = join($this->getColumn()->getSeparator(), $row->getStoresNames($row->getId()));
        return $data;
    }

}
