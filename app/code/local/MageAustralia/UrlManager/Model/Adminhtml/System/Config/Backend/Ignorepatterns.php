<?php

/**
 * Maho
 *
 * @category   MageAustralia
 * @package    MageAustralia_UrlManager
 * @copyright  Copyright (c) 2026 MageAustralia (https://mageaustralia.com.au)
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 */

declare(strict_types=1);

/**
 * Validates and normalises the 404-logging ignore patterns.
 *
 * Accepts newline- or comma-separated input, stores one pattern per line.
 */
class MageAustralia_UrlManager_Model_Adminhtml_System_Config_Backend_Ignorepatterns extends Mage_Core_Model_Config_Data
{
    #[\Override]
    protected function _beforeSave()
    {
        $value = (string) $this->getValue();

        if (trim($value) === '') {
            return parent::_beforeSave();
        }

        $errors = [];
        $patterns = [];

        foreach ($this->parsePatterns($value) as $pattern) {
            if (strlen($pattern) > 255) {
                $errors[] = 'Pattern too long (max 255 characters): ' . substr($pattern, 0, 50) . '...';
                continue;
            }

            if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $pattern)) {
                $errors[] = 'Pattern contains control characters: ' . addcslashes($pattern, "\x00..\x1F\x7F");
                continue;
            }

            $patterns[] = $pattern;
        }

        if ($errors !== []) {
            Mage::throwException("Invalid ignore patterns:\n" . implode("\n", $errors));
        }

        $this->setValue(implode("\n", $patterns));

        return parent::_beforeSave();
    }

    /**
     * @return string[] unique trimmed patterns
     */
    protected function parsePatterns(string $value): array
    {
        $lines = preg_split('/[\r\n,]+/', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_unique(array_filter(
            array_map('trim', $lines),
            static fn(string $pattern): bool => $pattern !== '',
        )));
    }
}
