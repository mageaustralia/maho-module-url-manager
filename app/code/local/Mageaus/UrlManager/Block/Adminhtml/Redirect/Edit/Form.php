<?php

/**
 * Maho
 *
 * @category   Mageaus
 * @package    Mageaus_UrlManager
 * @copyright  Copyright (c) 2025 Maho (https://mahocommerce.com)
 */

class Mageaus_UrlManager_Block_Adminhtml_Redirect_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
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
        $model = Mage::registry('current_redirect');

        $form = new Varien_Data_Form([
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', ['id' => $this->getRequest()->getParam('id')]),
            'method' => 'post',
            'enctype' => 'multipart/form-data',
        ]);

        $form->setUseContainer(true);

        $fieldset = $form->addFieldset('base_fieldset', [
            'legend' => Mage::helper('mageaus_urlmanager')->__('Redirect Information'),
            'class' => 'fieldset-wide',
        ]);

        if ($model->getId()) {
            $fieldset->addField('redirect_id', 'hidden', [
                'name' => 'redirect_id',
            ]);
        }
        $fieldset->addField('source_url', 'text', [
            'name' => 'source_url',
            'label' => Mage::helper('mageaus_urlmanager')->__('Source URL'),
            'title' => Mage::helper('mageaus_urlmanager')->__('Source URL'),
            'required' => true,
            'class' => 'validate-url',
        ]);
        $fieldset->addField('destination_url', 'text', [
            'name' => 'destination_url',
            'label' => Mage::helper('mageaus_urlmanager')->__('Destination URL'),
            'title' => Mage::helper('mageaus_urlmanager')->__('Destination URL'),
            'required' => true,
            'class' => 'validate-url',
        ]);
        $fieldset->addField('status_code', 'text', [
            'name' => 'status_code',
            'label' => Mage::helper('mageaus_urlmanager')->__('Status Code'),
            'title' => Mage::helper('mageaus_urlmanager')->__('Status Code'),
            'required' => true,
            'class' => 'validate-number',
        ]);
        $fieldset->addField('priority', 'text', [
            'name' => 'priority',
            'label' => Mage::helper('mageaus_urlmanager')->__('Priority'),
            'title' => Mage::helper('mageaus_urlmanager')->__('Priority'),
            'required' => true,
            'class' => 'validate-number',
        ]);
        $fieldset->addField('is_wildcard', 'select', [
            'name' => 'is_wildcard',
            'label' => Mage::helper('mageaus_urlmanager')->__('Is Wildcard'),
            'title' => Mage::helper('mageaus_urlmanager')->__('Is Wildcard'),
            'required' => true,
            'values' => [['value' => 1, 'label' => 'Yes'], ['value' => 0, 'label' => 'No']],
        ]);
        $fieldset->addField('is_active', 'select', [
            'name' => 'is_active',
            'label' => Mage::helper('mageaus_urlmanager')->__('Active'),
            'title' => Mage::helper('mageaus_urlmanager')->__('Active'),
            'required' => true,
            'values' => [['value' => 1, 'label' => 'Yes'], ['value' => 0, 'label' => 'No']],
        ]);
        $fieldset->addField('hit_count', 'text', [
            'name' => 'hit_count',
            'label' => Mage::helper('mageaus_urlmanager')->__('Hit Count'),
            'title' => Mage::helper('mageaus_urlmanager')->__('Hit Count'),
            'required' => true,
            'class' => 'validate-number',
        ]);
        $fieldset->addField('last_hit_at', 'datetime', [
            'name' => 'last_hit_at',
            'label' => Mage::helper('mageaus_urlmanager')->__('Last Hit At'),
            'title' => Mage::helper('mageaus_urlmanager')->__('Last Hit At'),
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
