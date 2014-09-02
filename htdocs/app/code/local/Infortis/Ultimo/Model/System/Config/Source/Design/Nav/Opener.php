<?php

class Infortis_Ultimo_Model_System_Config_Source_Design_Nav_Opener
{
    public function toOptionArray()
    {
		return array(
			array('value' => 'b',		'label' => Mage::helper('ultimo')->__('Black')),
            array('value' => 'w',		'label' => Mage::helper('ultimo')->__('White'))
        );
    }
}