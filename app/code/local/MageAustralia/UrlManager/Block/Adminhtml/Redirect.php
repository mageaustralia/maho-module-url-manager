<?php

/**
 * Maho
 *
 * @category   Mageaus
 * @package    MageAustralia_UrlManager
 * @copyright  Copyright (c) 2025 Maho (https://mahocommerce.com)
 * @license    https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

declare(strict_types=1);

class MageAustralia_UrlManager_Block_Adminhtml_Redirect extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_redirect';
        $this->_blockGroup = 'mageaustralia_urlmanager';
        $this->_headerText = Mage::helper('mageaustralia_urlmanager')->__('Manage Redirect');
        $this->_addButtonLabel = Mage::helper('mageaustralia_urlmanager')->__('Add Redirect');
        parent::__construct();
    }
}
