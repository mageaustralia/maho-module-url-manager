<?php

/**
 * Maho
 *
 * @category   Mageaus
 * @package    Mageaus_UrlManager
 * @copyright  Copyright (c) 2025 Maho (https://mahocommerce.com)
 */

class Mageaus_UrlManager_Block_Adminhtml_Redirect extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_redirect';
        $this->_blockGroup = 'mageaus_urlmanager';
        $this->_headerText = Mage::helper('mageaus_urlmanager')->__('Manage Redirect');
        $this->_addButtonLabel = Mage::helper('mageaus_urlmanager')->__('Add Redirect');
        parent::__construct();
    }
}
