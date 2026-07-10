<?php

/**
 * Maho
 *
 * @category   Mageaus
 * @package    MageAustralia_UrlManager
 * @copyright  Copyright (c) 2025 Maho (https://mahocommerce.com)
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 */

declare(strict_types=1);

/**
 * CSV Export Model
 *
 * Handles exporting redirects to CSV format:
 * old_url, new_url, status_code
 *
 * @category   Mageaus
 * @package    MageAustralia_UrlManager
 */
class MageAustralia_UrlManager_Model_Csv_Export
{
    /**
     * Export redirects to CSV string
     *
     * @param array|null $redirectIds Optional array of redirect IDs to export (null = all)
     * @param bool $includeHeader Whether to include header row
     * @return string CSV content
     */
    public function export(?array $redirectIds = null, bool $includeHeader = true): string
    {
        /** @var MageAustralia_UrlManager_Helper_Data $helper */
        $helper = Mage::helper('mageaustralia_urlmanager');

        $delimiter = $helper->getCsvDelimiter();
        $enclosure = $helper->getCsvEnclosure();

        // Load redirects
        /** @var MageAustralia_UrlManager_Model_Resource_Redirect_Collection $collection */
        $collection = Mage::getResourceModel('mageaustralia_urlmanager/redirect_collection')
            ->setOrder('priority', 'DESC');

        if ($redirectIds !== null) {
            $collection->addFieldToFilter('redirect_id', ['in' => $redirectIds]);
        }

        // Build CSV content
        $output = '';

        // Add header row
        if ($includeHeader) {
            $output .= $this->formatCsvRow(['old_url', 'new_url', 'status_code'], $delimiter, $enclosure);
        }

        // Add data rows
        foreach ($collection as $redirect) {
            /** @var MageAustralia_UrlManager_Model_Redirect $redirect */
            $output .= $this->formatCsvRow([
                $redirect->getSourceUrl(),
                $redirect->getDestinationUrl(),
                $redirect->getStatusCode(),
            ], $delimiter, $enclosure);
        }

        return $output;
    }

    /**
     * Export redirects to a file
     *
     * @param string $filePath Target file path
     * @param array|null $redirectIds Optional array of redirect IDs to export
     * @param bool $includeHeader Whether to include header row
     * @return int Number of redirects exported
     */
    public function exportToFile(string $filePath, ?array $redirectIds = null, bool $includeHeader = true): int
    {
        $csv = $this->export($redirectIds, $includeHeader);

        $result = file_put_contents($filePath, $csv);

        if ($result === false) {
            throw new Mage_Core_Exception('Failed to write CSV file: ' . $filePath);
        }

        // Count lines (excluding header if present)
        $lineCount = substr_count($csv, "\n");
        return $includeHeader ? $lineCount - 1 : $lineCount;
    }

    /**
     * Format a CSV row
     *
     * @param array $data Row data
     * @return string Formatted CSV row with newline
     */
    protected function formatCsvRow(array $data, string $delimiter, string $enclosure): string
    {
        $output = '';

        foreach ($data as $i => $field) {
            if ($i > 0) {
                $output .= $delimiter;
            }

            // Escape enclosure characters within the field
            $field = str_replace($enclosure, $enclosure . $enclosure, (string) $field);

            // Enclose field if it contains delimiter, enclosure, or newline
            if (
                str_contains((string) $field, $delimiter)
                || str_contains((string) $field, $enclosure)
                || str_contains((string) $field, "\n")
                || str_contains((string) $field, "\r")
            ) {
                $output .= $enclosure . $field . $enclosure;
            } else {
                $output .= $field;
            }
        }

        return $output . "\n";
    }
}
