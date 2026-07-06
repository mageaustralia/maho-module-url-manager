<?php

/**
 * Maho
 *
 * @category   Mageaus
 * @package    Mageaus_UrlManager
 * @copyright  Copyright (c) 2025 Maho (https://mahocommerce.com)
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 */

declare(strict_types=1);

/**
 * CSV Import Backend Model
 *
 * Processes CSV file uploads in System Configuration
 *
 * @category   Mageaus
 * @package    Mageaus_UrlManager
 */
class Mageaus_UrlManager_Model_Adminhtml_System_Config_Backend_Csvimport extends Mage_Core_Model_Config_Data
{
    /**
     * Process CSV file upload before save
     *
     * @return Mageaus_UrlManager_Model_Adminhtml_System_Config_Backend_Csvimport
     */
    #[\Override]
    protected function _beforeSave()
    {
        $value = $this->getValue();

        Mage::log('CSV Import: _beforeSave() called', Mage::LOG_DEBUG, 'mageaus_urlmanager.log');
        Mage::log('CSV Import: $_FILES data: ' . print_r($_FILES, true), Mage::LOG_DEBUG, 'mageaus_urlmanager.log');

        // Check if file was uploaded
        if (isset($_FILES['groups']['tmp_name'][$this->getGroupId()]['fields'][$this->getField()]['value'])
            && $_FILES['groups']['tmp_name'][$this->getGroupId()]['fields'][$this->getField()]['value']
        ) {
            $uploadedFile = $_FILES['groups']['tmp_name'][$this->getGroupId()]['fields'][$this->getField()]['value'];
            $fileName = $_FILES['groups']['name'][$this->getGroupId()]['fields'][$this->getField()]['value'];

            Mage::log('CSV Import: Processing file: ' . $fileName, Mage::LOG_INFO, 'mageaus_urlmanager.log');
            Mage::log('CSV Import: Temp file path: ' . $uploadedFile, Mage::LOG_DEBUG, 'mageaus_urlmanager.log');

            try {
                // Validate file extension
                $fileExtension = strtolower(pathinfo((string) $fileName, PATHINFO_EXTENSION));
                if ($fileExtension !== 'csv') {
                    Mage::throwException('Only CSV files are allowed');
                }

                // Get skip duplicates setting
                $skipDuplicates = Mage::getStoreConfigFlag('mageaus_urlmanager/csv/skip_duplicates');

                // Import redirects
                /** @var Mageaus_UrlManager_Model_Csv_Import $importer */
                $importer = Mage::getModel('mageaus_urlmanager/csv_import');
                $results = $importer->import($uploadedFile, $skipDuplicates);

                Mage::log('CSV Import: Import results: ' . print_r($results, true), Mage::LOG_INFO, 'mageaus_urlmanager.log');

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
                    'CSV Import: ' . implode(', ', $messages),
                );

                // Log any errors
                if (!empty($results['errors'])) {
                    foreach ($results['errors'] as $error) {
                        Mage::getSingleton('adminhtml/session')->addWarning($error);
                    }
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    'CSV Import failed: ' . $e->getMessage(),
                );
            }
        }

        // Don't save the file path to config - just set empty value
        $this->setValue('');

        return parent::_beforeSave();
    }
}
