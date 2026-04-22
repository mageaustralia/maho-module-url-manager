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

    public function indexAction(): void
    {
        $this->_title($this->__('Manage Notfound_log'));
        $this->_initAction();
        $this->renderLayout();
    }

    public function gridAction(): void
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function newAction(): void
    {
        $this->_forward('edit');
    }

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
