<?php

/**
 * Maho
 *
 * @category   Mageaus
 * @package    Mageaus_UrlManager
 * @copyright  Copyright (c) 2025 Maho (https://mahocommerce.com)
 */

class Mageaus_UrlManager_Block_Adminhtml_Redirect_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('redirectGrid');
        $this->setDefaultSort('redirect_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    #[\Override]
    protected function _prepareCollection(): static
    {
        $collection = Mage::getModel('mageaus_urlmanager/redirect')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    #[\Override]
    protected function _prepareColumns(): static
    {
        $this->addColumn('redirect_id', [
            'header' => Mage::helper('mageaus_urlmanager')->__('ID'),
            'width' => '50px',
            'index' => 'redirect_id',
        ]);

        $this->addColumn('source_url', [
            'header' => Mage::helper('mageaus_urlmanager')->__('Source URL'),
            'index' => 'source_url',
        ]);
        $this->addColumn('destination_url', [
            'header' => Mage::helper('mageaus_urlmanager')->__('Destination URL'),
            'index' => 'destination_url',
        ]);
        $this->addColumn('status_code', [
            'header' => Mage::helper('mageaus_urlmanager')->__('Status Code'),
            'index' => 'status_code',
            'type' => 'number',
        ]);
        $this->addColumn('priority', [
            'header' => Mage::helper('mageaus_urlmanager')->__('Priority'),
            'index' => 'priority',
            'type' => 'number',
        ]);
        $this->addColumn('is_wildcard', [
            'header' => Mage::helper('mageaus_urlmanager')->__('Is Wildcard'),
            'index' => 'is_wildcard',
            'type' => 'options',
            'options' => ['1' => 'Yes', '0' => 'No'],
        ]);
        $this->addColumn('is_active', [
            'header' => Mage::helper('mageaus_urlmanager')->__('Active'),
            'index' => 'is_active',
            'type' => 'options',
            'options' => ['1' => 'Yes', '0' => 'No'],
        ]);
        $this->addColumn('hit_count', [
            'header' => Mage::helper('mageaus_urlmanager')->__('Hit Count'),
            'index' => 'hit_count',
            'type' => 'number',
        ]);
        $this->addColumn('last_hit_at', [
            'header' => Mage::helper('mageaus_urlmanager')->__('Last Hit At'),
            'index' => 'last_hit_at',
            'type' => 'datetime',
        ]);

        $this->addColumn('action', [
            'header' => Mage::helper('mageaus_urlmanager')->__('Action'),
            'width' => '50px',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => [[
                'caption' => Mage::helper('mageaus_urlmanager')->__('Edit'),
                'url' => ['base' => '*/*/edit'],
                'field' => 'id',
            ]],
            'filter' => false,
            'sortable' => false,
        ]);

        return parent::_prepareColumns();
    }

    #[\Override]
    protected function _prepareMassaction(): static
    {
        $this->setMassactionIdField('redirect_id');
        $this->getMassactionBlock()->setFormFieldName('ids');

        $this->getMassactionBlock()->addItem('delete', [
            'label' => Mage::helper('mageaus_urlmanager')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('mageaus_urlmanager')->__('Are you sure?'),
        ]);

        return $this;
    }

    #[\Override]
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }

    #[\Override]
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }
}
