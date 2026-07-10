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

class MageAustralia_UrlManager_Block_Adminhtml_Notfoundlog_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    #[\Override]
    protected function _prepareLayout(): static
    {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            if ($head = $this->getLayout()->getBlock('head')) {
                $head->setCanLoadWysiwyg(true);
            }
        }
        return $this;
    }

    #[\Override]
    protected function _prepareForm(): static
    {
        $model = Mage::registry('current_notfoundlog');

        $form = new Maho\Data\Form([
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', ['id' => $this->getRequest()->getParam('id')]),
            'method' => 'post',
            'enctype' => 'multipart/form-data',
        ]);

        $form->setUseContainer(true);

        $fieldset = $form->addFieldset('base_fieldset', [
            'legend' => Mage::helper('mageaustralia_urlmanager')->__('Notfound_log Information'),
            'class' => 'fieldset-wide',
        ]);

        if ($model->getId()) {
            $fieldset->addField('notfound_log_id', 'hidden', [
                'name' => 'notfound_log_id',
            ]);
        }
        $fieldset->addField('request_url', 'text', [
            'name' => 'request_url',
            'label' => Mage::helper('mageaustralia_urlmanager')->__('Request URL'),
            'title' => Mage::helper('mageaustralia_urlmanager')->__('Request URL'),
            'required' => true,
            'class' => 'validate-url',
        ]);
        $fieldset->addField('referer_url', 'text', [
            'name' => 'referer_url',
            'label' => Mage::helper('mageaustralia_urlmanager')->__('Referer URL'),
            'title' => Mage::helper('mageaustralia_urlmanager')->__('Referer URL'),
            'required' => false,
            'class' => 'validate-url',
        ]);
        $fieldset->addField('user_agent', 'editor', [
            'name' => 'user_agent',
            'label' => Mage::helper('mageaustralia_urlmanager')->__('User Agent'),
            'title' => Mage::helper('mageaustralia_urlmanager')->__('User Agent'),
            'required' => false,
            'wysiwyg' => true,
            'config' => Mage::getSingleton('cms/wysiwyg_config')->getConfig(),
            'style' => 'height:300px',
        ]);
        $fieldset->addField('ip_address', 'text', [
            'name' => 'ip_address',
            'label' => Mage::helper('mageaustralia_urlmanager')->__('IP Address'),
            'title' => Mage::helper('mageaustralia_urlmanager')->__('IP Address'),
            'required' => false,
        ]);
        $fieldset->addField('store_id', 'text', [
            'name' => 'store_id',
            'label' => Mage::helper('mageaustralia_urlmanager')->__('Store ID'),
            'title' => Mage::helper('mageaustralia_urlmanager')->__('Store ID'),
            'required' => true,
        ]);
        $fieldset->addField('hit_count', 'text', [
            'name' => 'hit_count',
            'label' => Mage::helper('mageaustralia_urlmanager')->__('Hit Count'),
            'title' => Mage::helper('mageaustralia_urlmanager')->__('Hit Count'),
            'required' => true,
            'class' => 'validate-number',
        ]);
        $fieldset->addField('suggested_product_id', 'text', [
            'name' => 'suggested_product_id',
            'label' => Mage::helper('mageaustralia_urlmanager')->__('Suggested Product ID'),
            'title' => Mage::helper('mageaustralia_urlmanager')->__('Suggested Product ID'),
            'required' => false,
        ]);
        $fieldset->addField('last_hit_at', 'datetime', [
            'name' => 'last_hit_at',
            'label' => Mage::helper('mageaustralia_urlmanager')->__('Last Hit At'),
            'title' => Mage::helper('mageaustralia_urlmanager')->__('Last Hit At'),
            'required' => false,
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => 'Y-MM-dd HH:mm:ss',
            'time' => 'true',
        ]);

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
