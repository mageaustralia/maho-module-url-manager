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

class MageAustralia_UrlManager_Block_Adminhtml_Notfoundlog_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('notfound_logGrid');
        $this->setDefaultSort('notfound_log_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    #[\Override]
    protected function _prepareCollection(): static
    {
        $collection = Mage::getModel('mageaustralia_urlmanager/notfoundlog')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    #[\Override]
    protected function _prepareColumns(): static
    {
        $this->addColumn('notfound_log_id', [
            'header' => Mage::helper('mageaustralia_urlmanager')->__('ID'),
            'width' => '50px',
            'index' => 'notfound_log_id',
        ]);

        $this->addColumn('request_url', [
            'header' => Mage::helper('mageaustralia_urlmanager')->__('Request URL'),
            'index' => 'request_url',
        ]);
        $this->addColumn('referer_url', [
            'header' => Mage::helper('mageaustralia_urlmanager')->__('Referer URL'),
            'index' => 'referer_url',
        ]);
        $this->addColumn('ip_address', [
            'header' => Mage::helper('mageaustralia_urlmanager')->__('IP Address'),
            'index' => 'ip_address',
        ]);
        $this->addColumn('store_id', [
            'header' => Mage::helper('mageaustralia_urlmanager')->__('Store ID'),
            'index' => 'store_id',
        ]);
        $this->addColumn('hit_count', [
            'header' => Mage::helper('mageaustralia_urlmanager')->__('Hit Count'),
            'index' => 'hit_count',
            'type' => 'number',
        ]);
        $this->addColumn('suggested_product_id', [
            'header' => Mage::helper('mageaustralia_urlmanager')->__('Suggested Product ID'),
            'index' => 'suggested_product_id',
        ]);
        $this->addColumn('last_hit_at', [
            'header' => Mage::helper('mageaustralia_urlmanager')->__('Last Hit At'),
            'index' => 'last_hit_at',
            'type' => 'datetime',
        ]);

        $this->addColumn('action', [
            'header' => Mage::helper('mageaustralia_urlmanager')->__('Action'),
            'width' => '50px',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => [[
                'caption' => Mage::helper('mageaustralia_urlmanager')->__('Edit'),
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
        $this->setMassactionIdField('notfound_log_id');
        $this->getMassactionBlock()->setFormFieldName('ids');

        $this->getMassactionBlock()->addItem('delete', [
            'label' => Mage::helper('mageaustralia_urlmanager')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('mageaustralia_urlmanager')->__('Are you sure?'),
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
