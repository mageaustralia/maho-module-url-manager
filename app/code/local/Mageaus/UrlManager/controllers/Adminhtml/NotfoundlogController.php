<?php

/**
 * Maho
 *
 * @category   Mageaus
 * @package    Mageaus_UrlManager
 * @copyright  Copyright (c) 2025 Maho (https://mahocommerce.com)
 */

/**
 * Notfound_log Admin Controller
 *
 * @category   Mageaus
 * @package    Mageaus_UrlManager
 */
class Mageaus_UrlManager_Adminhtml_NotfoundlogController extends Mage_Adminhtml_Controller_Action
{
    public const ADMIN_RESOURCE = 'mageaus_urlmanager/notfoundlog';

    #[\Override]
    public function preDispatch()
    {
        // CSRF: save (edit form), delete (Form_Container button includes
        // form_key), and massDelete (grid massaction) all carry form_key.
        $this->_setForcedFormKeyActions(['save', 'delete', 'massDelete']);
        return parent::preDispatch();
    }

    #[\Maho\Config\Route('/urlmanager/adminhtml_notfoundlog/_init', name: 'urlmanager.adminhtml_notfoundlog._init')]
    protected function _initAction(): static
    {
        $this->loadLayout()
            ->_setActiveMenu('mageaus_urlmanager/notfoundlog')
            ->_addBreadcrumb(
                Mage::helper('mageaus_urlmanager')->__('Notfound_log'),
                Mage::helper('mageaus_urlmanager')->__('Notfound_log'),
            );
        return $this;
    }

    #[\Maho\Config\Route('/urlmanager/adminhtml_notfoundlog', name: 'urlmanager.adminhtml_notfoundlog')]
    #[\Maho\Config\Route('/urlmanager/adminhtml_notfoundlog/index', name: 'urlmanager.adminhtml_notfoundlog.index')]
    #[\Maho\Config\Route('/admin/notfoundlog/index')]
    public function indexAction(): void
    {
        $this->_title($this->__('Manage Notfound_log'));
        $this->_initAction();
        $this->renderLayout();
    }

    #[\Maho\Config\Route('/urlmanager/adminhtml_notfoundlog/grid', name: 'urlmanager.adminhtml_notfoundlog.grid')]
    #[\Maho\Config\Route('/admin/notfoundlog/grid')]
    public function gridAction(): void
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    #[\Maho\Config\Route('/urlmanager/adminhtml_notfoundlog/new', name: 'urlmanager.adminhtml_notfoundlog.new')]
    #[\Maho\Config\Route('/admin/notfoundlog/new')]
    public function newAction(): void
    {
        $this->_forward('edit');
    }

    #[\Maho\Config\Route('/urlmanager/adminhtml_notfoundlog/edit', name: 'urlmanager.adminhtml_notfoundlog.edit')]
    #[\Maho\Config\Route('/admin/notfoundlog/edit')]
    public function editAction(): void
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('mageaus_urlmanager/notfoundlog');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('mageaus_urlmanager')->__('Notfound_log does not exist'),
                );
                $this->_redirect('*/*/');
                return;
            }
        }

        $this->_title($model->getId() ? $model->getData('request_url') : $this->__('New Notfound_log'));

        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register('current_notfound_log', $model);

        $this->_initAction();
        $this->renderLayout();
    }

    #[\Maho\Config\Route('/urlmanager/adminhtml_notfoundlog/save', name: 'urlmanager.adminhtml_notfoundlog.save')]
    #[\Maho\Config\Route('/admin/notfoundlog/save')]
    public function saveAction(): void
    {
        if ($data = $this->getRequest()->getPost()) {
            $id = $this->getRequest()->getParam('id');
            $model = Mage::getModel('mageaus_urlmanager/notfoundlog');

            if ($id) {
                $model->load($id);
            }

            $model->setData($data);

            try {
                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('mageaus_urlmanager')->__('Notfound_log was successfully saved'),
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $model->getId()]);
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    #[\Maho\Config\Route('/urlmanager/adminhtml_notfoundlog/delete', name: 'urlmanager.adminhtml_notfoundlog.delete')]
    #[\Maho\Config\Route('/admin/notfoundlog/delete')]
    public function deleteAction(): void
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('mageaus_urlmanager/notfoundlog');
                $model->load($id);
                $model->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('mageaus_urlmanager')->__('Notfound_log was successfully deleted'),
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', ['id' => $id]);
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    #[\Maho\Config\Route('/urlmanager/adminhtml_notfoundlog/massDelete', name: 'urlmanager.adminhtml_notfoundlog.massDelete')]
    #[\Maho\Config\Route('/admin/notfoundlog/massDelete')]
    public function massDeleteAction(): void
    {
        $ids = $this->getRequest()->getParam('ids');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('mageaus_urlmanager')->__('Please select item(s)'),
            );
        } else {
            try {
                foreach ($ids as $id) {
                    $model = Mage::getModel('mageaus_urlmanager/notfoundlog')->load($id);
                    $model->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('mageaus_urlmanager')->__(
                        'Total of %d record(s) were successfully deleted',
                        count($ids),
                    ),
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    #[\Override]
    protected function _isAllowed(): bool
    {
        return Mage::getSingleton('admin/session')->isAllowed(self::ADMIN_RESOURCE);
    }
}
