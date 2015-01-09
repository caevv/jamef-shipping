<?php
/**
 * Cae Vecchi
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL).
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Jamef
 * @package    Jamef_Shipping
 * @copyright  Copyright (c) 2009 Cae Vecchi [ carlos@vecchi.me ]
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Jamef_Shipping_Model_FreeMethods
{

    public function toOptionArray()
    {
        return array(
            array('value'=>41106, 'label'=>Mage::helper('adminhtml')->__('PAC')),
            array('value'=>40010, 'label'=>Mage::helper('adminhtml')->__('Sedex')),
            array('value'=>40215, 'label'=>Mage::helper('adminhtml')->__('Sedex 10')),
            array('value'=>40290, 'label'=>Mage::helper('adminhtml')->__('Sedex HOJE')),
            array('value'=>81019, 'label'=>Mage::helper('adminhtml')->__('E-Sedex'))
        );
    }

}