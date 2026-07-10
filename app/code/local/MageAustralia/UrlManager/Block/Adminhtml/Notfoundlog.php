<?php

/**
 * Maho
 *
 * @category   MageAustralia
 * @package    MageAustralia_UrlManager
 * @copyright  Copyright (c) 2025 Maho (https://mahocommerce.com)
 * @license    https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

declare(strict_types=1);

class MageAustralia_UrlManager_Block_Adminhtml_Notfoundlog extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_notfoundlog';
        $this->_blockGroup = 'mageaustralia_urlmanager';
        $this->_headerText = Mage::helper('mageaustralia_urlmanager')->__('404 Not Found Log');
        parent::__construct();
        $this->_removeButton('add'); // Read-only log - no adding entries
    }
}
