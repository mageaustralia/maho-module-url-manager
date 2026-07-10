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

/**
 * Redirect Admin Controller
 *
 * @category   Mageaus
 * @package    MageAustralia_UrlManager
 */
class MageAustralia_UrlManager_Adminhtml_RedirectController extends Mage_Adminhtml_Controller_Action
{
    public const ADMIN_RESOURCE = 'catalog/mageaustralia_urlmanager/redirect';

    #[\Override]
    public function preDispatch()
    {
        // CSRF: save (edit form), delete (Form_Container button includes
        // form_key), and massDelete (grid massaction) all carry form_key.
        $this->_setForcedFormKeyActions(['save', 'delete', 'massDelete']);
        return parent::preDispatch();
    }

    #[\Maho\Config\Route('/urlmanager/adminhtml_redirect/_init', name: 'urlmanager.adminhtml_redirect._init')]
    protected function _initAction(): static
    {
        $this->loadLayout()
            ->_setActiveMenu('mageaustralia_urlmanager/redirect')
            ->_addBreadcrumb(
                Mage::helper('mageaustralia_urlmanager')->__('Redirect'),
                Mage::helper('mageaustralia_urlmanager')->__('Redirect'),
            );
        return $this;
    }

    #[\Maho\Config\Route('/urlmanager/adminhtml_redirect', name: 'urlmanager.adminhtml_redirect')]
    #[\Maho\Config\Route('/urlmanager/adminhtml_redirect/index', name: 'urlmanager.adminhtml_redirect.index')]
    #[\Maho\Config\Route('/admin/redirect/index')]
    public function indexAction(): void
    {
        $this->_title($this->__('Manage Redirect'));
        $this->_initAction();
        $this->renderLayout();
    }

    #[\Maho\Config\Route('/urlmanager/adminhtml_redirect/grid', name: 'urlmanager.adminhtml_redirect.grid')]
    #[\Maho\Config\Route('/admin/redirect/grid')]
    public function gridAction(): void
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    #[\Maho\Config\Route('/urlmanager/adminhtml_redirect/new', name: 'urlmanager.adminhtml_redirect.new')]
    #[\Maho\Config\Route('/admin/redirect/new')]
    public function newAction(): void
    {
        $this->_forward('edit');
    }

    #[\Maho\Config\Route('/urlmanager/adminhtml_redirect/edit', name: 'urlmanager.adminhtml_redirect.edit')]
    #[\Maho\Config\Route('/admin/redirect/edit')]
    public function editAction(): void
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('mageaustralia_urlmanager/redirect');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('mageaustralia_urlmanager')->__('Redirect does not exist'),
                );
                $this->_redirect('*/*/');
                return;
            }
        }

        $this->_title($model->getId() ? $model->getData('source_url') : $this->__('New Redirect'));

        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register('current_redirect', $model);

        $this->_initAction();
        $this->renderLayout();
    }

    #[\Maho\Config\Route('/urlmanager/adminhtml_redirect/save', name: 'urlmanager.adminhtml_redirect.save')]
    #[\Maho\Config\Route('/admin/redirect/save')]
    public function saveAction(): void
    {
        if ($data = $this->getRequest()->getPost()) {
            $id = $this->getRequest()->getParam('id');
            $model = Mage::getModel('mageaustralia_urlmanager/redirect');

            if ($id) {
                $model->load($id);
            }

            $model->setData($data);

            try {
                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('mageaustralia_urlmanager')->__('Redirect was successfully saved'),
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

    #[\Maho\Config\Route('/urlmanager/adminhtml_redirect/delete', name: 'urlmanager.adminhtml_redirect.delete')]
    #[\Maho\Config\Route('/admin/redirect/delete')]
    public function deleteAction(): void
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('mageaustralia_urlmanager/redirect');
                $model->load($id);
                $model->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('mageaustralia_urlmanager')->__('Redirect was successfully deleted'),
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', ['id' => $id]);
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    #[\Maho\Config\Route('/urlmanager/adminhtml_redirect/massDelete', name: 'urlmanager.adminhtml_redirect.massDelete')]
    #[\Maho\Config\Route('/admin/redirect/massDelete')]
    public function massDeleteAction(): void
    {
        $ids = $this->getRequest()->getParam('ids');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('mageaustralia_urlmanager')->__('Please select item(s)'),
            );
        } else {
            try {
                foreach ($ids as $id) {
                    $model = Mage::getModel('mageaustralia_urlmanager/redirect')->load($id);
                    $model->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('mageaustralia_urlmanager')->__(
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

    #[\Maho\Config\Route('/urlmanager/adminhtml_redirect/importCsv', name: 'urlmanager.adminhtml_redirect.importCsv')]
    #[\Maho\Config\Route('/admin/redirect/importCsv')]
    public function importCsvAction(): void
    {
        if (!$this->getRequest()->isPost()) {
            $this->_redirect('*/*/index');
            return;
        }

        try {
            // Check if file was uploaded
            if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('No file uploaded or upload error occurred');
            }

            $uploadedFile = $_FILES['csv_file']['tmp_name'];

            // Validate file extension
            $fileName = $_FILES['csv_file']['name'];
            $fileExtension = strtolower(pathinfo((string) $fileName, PATHINFO_EXTENSION));

            if ($fileExtension !== 'csv') {
                throw new Exception('Only CSV files are allowed');
            }

            // Import redirects
            /** @var MageAustralia_UrlManager_Model_Csv_Import $importer */
            $importer = Mage::getModel('mageaustralia_urlmanager/csv_import');

            $skipDuplicates = (bool) $this->getRequest()->getPost('skip_duplicates', true);
            $results = $importer->import($uploadedFile, $skipDuplicates);

            // Build success message
            $messages = [
                sprintf('%d redirects imported', $results['imported']),
            ];

            if ($results['updated'] > 0) {
                $messages[] = sprintf('%d redirects updated', $results['updated']);
            }

            if ($results['skipped'] > 0) {
                $messages[] = sprintf('%d redirects skipped (duplicates)', $results['skipped']);
            }

            Mage::getSingleton('adminhtml/session')->addSuccess(
                implode(', ', $messages),
            );

            // Log any errors
            if (!empty($results['errors'])) {
                foreach ($results['errors'] as $error) {
                    Mage::getSingleton('adminhtml/session')->addWarning($error);
                }
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(
                'Import failed: ' . $e->getMessage(),
            );
        }

        $this->_redirect('*/*/index');
    }

    #[\Maho\Config\Route('/urlmanager/adminhtml_redirect/exportCsv', name: 'urlmanager.adminhtml_redirect.exportCsv')]
    #[\Maho\Config\Route('/admin/redirect/exportCsv')]
    public function exportCsvAction(): void
    {
        try {
            // Get selected redirect IDs from request
            $ids = $this->getRequest()->getParam('ids', null);

            // Export to CSV
            /** @var MageAustralia_UrlManager_Model_Csv_Export $exporter */
            $exporter = Mage::getModel('mageaustralia_urlmanager/csv_export');
            $csv = $exporter->export($ids);

            // Send CSV as download
            $fileName = 'redirects_' . date('Y-m-d_H-i-s') . '.csv';

            $this->_prepareDownloadResponse($fileName, $csv, 'text/csv');
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(
                'Export failed: ' . $e->getMessage(),
            );
            $this->_redirect('*/*/index');
        }
    }

    #[\Override]
    protected function _isAllowed(): bool
    {
        return Mage::getSingleton('admin/session')->isAllowed(self::ADMIN_RESOURCE);
    }
}
