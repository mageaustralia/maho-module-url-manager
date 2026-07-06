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
 * CSV Import Model
 *
 * Handles importing redirects from CSV format:
 * old_url, new_url, status_code (301/302)
 *
 * @category   Mageaus
 * @package    Mageaus_UrlManager
 */
class Mageaus_UrlManager_Model_Csv_Import
{
    /**
     * Import redirects from CSV file
     *
     * @param string $filePath Path to CSV file
     * @param bool $skipDuplicates Whether to skip duplicate source URLs
     * @return array Import results
     */
    public function import(string $filePath, bool $skipDuplicates = true): array
    {
        Mage::log('CSV Import Model: Starting import from: ' . $filePath, Mage::LOG_INFO, 'mageaus_urlmanager.log');
        Mage::log('CSV Import Model: Skip duplicates: ' . ($skipDuplicates ? 'yes' : 'no'), Mage::LOG_DEBUG, 'mageaus_urlmanager.log');

        if (!file_exists($filePath)) {
            Mage::log('CSV Import Model: ERROR - File does not exist!', Mage::LOG_ERR, 'mageaus_urlmanager.log');
            throw new Mage_Core_Exception('CSV file does not exist: ' . $filePath);
        }

        /** @var Mageaus_UrlManager_Helper_Data $helper */
        $helper = Mage::helper('mageaus_urlmanager');

        $delimiter = $helper->getCsvDelimiter();
        $enclosure = $helper->getCsvEnclosure();

        Mage::log('CSV Import Model: Delimiter: ' . $delimiter . ', Enclosure: ' . $enclosure, Mage::LOG_DEBUG, 'mageaus_urlmanager.log');

        $results = [
            'total' => 0,
            'imported' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new Mage_Core_Exception('Failed to open CSV file');
        }

        $lineNumber = 0;

        try {
            while (($data = fgetcsv($handle, 0, $delimiter, $enclosure)) !== false) {
                $lineNumber++;

                Mage::log('CSV Import Model: Line ' . $lineNumber . ' raw data: ' . print_r($data, true), Mage::LOG_DEBUG, 'mageaus_urlmanager.log');

                // Skip header row if it exists
                if ($lineNumber === 1 && strtolower((string) $data[0]) === 'old_url') {
                    Mage::log('CSV Import Model: Skipping header row', Mage::LOG_DEBUG, 'mageaus_urlmanager.log');
                    continue;
                }

                $results['total']++;

                try {
                    $rowResult = $this->importRow($data, $skipDuplicates);
                    Mage::log('CSV Import Model: Line ' . $lineNumber . ' result: ' . $rowResult, Mage::LOG_DEBUG, 'mageaus_urlmanager.log');

                    if ($rowResult === 'imported') {
                        $results['imported']++;
                    } elseif ($rowResult === 'updated') {
                        $results['updated']++;
                    } elseif ($rowResult === 'skipped') {
                        $results['skipped']++;
                    }
                } catch (Exception $e) {
                    $results['errors'][] = sprintf(
                        'Line %d: %s',
                        $lineNumber,
                        $e->getMessage(),
                    );
                }
            }
        } finally {
            fclose($handle);
        }

        return $results;
    }

    /**
     * Import a single row of data
     *
     * @param array $data Row data [old_url, new_url, status_code]
     * @return string 'imported', 'updated', or 'skipped'
     * @throws Mage_Core_Exception
     */
    protected function importRow(array $data, bool $skipDuplicates): string
    {
        // Validate row has required columns
        if (count($data) < 2) {
            throw new Mage_Core_Exception('Row must have at least 2 columns (old_url, new_url)');
        }

        $sourceUrl = trim((string) $data[0]);
        $destinationUrl = trim((string) $data[1]);
        $statusCode = isset($data[2]) ? (int) trim($data[2]) : 301;

        // Validate URLs
        if (empty($sourceUrl)) {
            throw new Mage_Core_Exception('Source URL cannot be empty');
        }

        if (empty($destinationUrl)) {
            throw new Mage_Core_Exception('Destination URL cannot be empty');
        }

        // Validate status code
        if (!in_array($statusCode, [301, 302, 307])) {
            $statusCode = 301; // Default to 301 if invalid
        }

        // Check for existing redirect with same source URL
        /** @var Mageaus_UrlManager_Model_Resource_Redirect_Collection $collection */
        $collection = Mage::getResourceModel('mageaus_urlmanager/redirect_collection')
            ->addFieldToFilter('source_url', $sourceUrl);

        if ($collection->getSize() > 0) {
            if ($skipDuplicates) {
                return 'skipped';
            }

            // Update existing redirect
            /** @var Mageaus_UrlManager_Model_Redirect $redirect */
            $redirect = $collection->getFirstItem();
            $redirect->setDestinationUrl($destinationUrl);
            $redirect->setStatusCode($statusCode);
            $redirect->save();

            return 'updated';
        }

        // Create new redirect
        $redirect = Mage::getModel('mageaus_urlmanager/redirect');
        $redirect->setData([
            'source_url' => $sourceUrl,
            'destination_url' => $destinationUrl,
            'status_code' => $statusCode,
            'priority' => 0,
            'is_wildcard' => $this->detectWildcard($sourceUrl),
            'is_active' => 1,
            'hit_count' => 0,
        ]);

        $redirect->save();

        return 'imported';
    }

    /**
     * Detect if URL contains wildcard character
     */
    protected function detectWildcard(string $url): int
    {
        /** @var Mageaus_UrlManager_Helper_Data $helper */
        $helper = Mage::helper('mageaus_urlmanager');
        $wildcardChar = $helper->getWildcardCharacter();

        return str_contains($url, $wildcardChar) ? 1 : 0;
    }
}
