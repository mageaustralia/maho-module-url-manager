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

class MageAustralia_UrlManager_Block_Adminhtml_Notfoundlog_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'mageaustralia_urlmanager';
        $this->_controller = 'adminhtml_notfoundlog';

        $this->_updateButton('save', 'label', Mage::helper('mageaustralia_urlmanager')->__('Save 404 Log Entry'));
        $this->_updateButton('delete', 'label', Mage::helper('mageaustralia_urlmanager')->__('Delete 404 Log Entry'));

        $this->_addButton('saveandcontinue', [
            'label' => Mage::helper('mageaustralia_urlmanager')->__('Save and Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
        ], -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit() {
                editForm.submit($('edit_form').action + 'back/edit/');
            }
        ";
    }

    #[\Override]
    public function getHeaderText()
    {
        if (Mage::registry('current_notfoundlog')->getId()) {
            return Mage::helper('mageaustralia_urlmanager')->__('Edit 404 Log Entry');
        } else {
            return Mage::helper('mageaustralia_urlmanager')->__('New 404 Log Entry');
        }
    }

    /**
     * Base container falls back to the deprecated getSaveUrl() when this
     * is absent (E_USER_DEPRECATED per admin edit-page render).
     */
    #[\Override]
    public function getFormActionUrl(): string
    {
        return $this->getUrl('*/*/save');
    }
}
