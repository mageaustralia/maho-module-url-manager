<?php

/**
 * Maho
 *
 * @category   Mageaus
 * @package    Mageaus_UrlManager
 * @copyright  Copyright (c) 2025 Maho (https://mahocommerce.com) & Mageaustralia (https://mageaustralia.com.au)
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 */

declare(strict_types=1);

/**
 * UrlManager Data Helper
 *
 * Provides configuration access methods
 *
 * @category   Mageaus
 * @package    Mageaus_UrlManager
 */
class Mageaus_UrlManager_Helper_Data extends Mage_Core_Helper_Abstract
{
    // Configuration paths
    public const XML_PATH_GENERAL_ENABLED = 'mageaus_urlmanager/redirects/enabled';
    public const XML_PATH_WILDCARD_CHARACTER = 'mageaus_urlmanager/redirects/wildcard_character';
    public const XML_PATH_CASE_SENSITIVE = 'mageaus_urlmanager/redirects/case_sensitive';
    public const XML_PATH_STRIP_QUERY_STRING = 'mageaus_urlmanager/redirects/strip_query_string';

    public const XML_PATH_404_LOGGING_ENABLED = 'mageaus_urlmanager/logging/enabled';
    public const XML_PATH_404_LOG_BOTS = 'mageaus_urlmanager/logging/log_bots';
    public const XML_PATH_404_MAX_LOG_ENTRIES = 'mageaus_urlmanager/logging/max_log_entries';

    public const XML_PATH_SUGGESTIONS_ENABLED = 'mageaus_urlmanager/suggestions/enabled';
    public const XML_PATH_SUGGESTIONS_MAX = 'mageaus_urlmanager/suggestions/max_suggestions';
    public const XML_PATH_SUGGESTIONS_USE_MEILISEARCH = 'mageaus_urlmanager/suggestions/use_meilisearch';

    public const XML_PATH_AUTO_DISABLED_PRODUCTS = 'mageaus_urlmanager/auto_redirects/disabled_products';
    public const XML_PATH_AUTO_NOT_VISIBLE_PRODUCTS = 'mageaus_urlmanager/auto_redirects/not_visible_products';
    public const XML_PATH_AUTO_DISABLED_CATEGORIES = 'mageaus_urlmanager/auto_redirects/disabled_categories';

    public const XML_PATH_CSV_DELIMITER = 'mageaus_urlmanager/csv/delimiter';
    public const XML_PATH_CSV_ENCLOSURE = 'mageaus_urlmanager/csv/enclosure';
    public const XML_PATH_CSV_SKIP_DUPLICATES = 'mageaus_urlmanager/csv/skip_duplicates';

    public const XML_PATH_EMAIL_ENABLED = 'mageaus_urlmanager/email_notifications/enabled';
    public const XML_PATH_EMAIL_FREQUENCY = 'mageaus_urlmanager/email_notifications/frequency';
    public const XML_PATH_EMAIL_RECIPIENT = 'mageaus_urlmanager/email_notifications/recipient_email';
    public const XML_PATH_EMAIL_MINIMUM_HITS = 'mageaus_urlmanager/email_notifications/minimum_hits';

    /**
     * Check if URL Manager is enabled
     */
    public function isEnabled(?int $storeId = null): bool
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_GENERAL_ENABLED, $storeId);
    }

    /**
     * Check if disabled products should be auto-redirected
     */
    public function shouldRedirectDisabledProducts(?int $storeId = null): bool
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_AUTO_DISABLED_PRODUCTS, $storeId);
    }

    /**
     * Check if not visible products should be auto-redirected
     */
    public function shouldRedirectNotVisibleProducts(?int $storeId = null): bool
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_AUTO_NOT_VISIBLE_PRODUCTS, $storeId);
    }

    /**
     * Check if disabled categories should be auto-redirected
     */
    public function shouldRedirectDisabledCategories(?int $storeId = null): bool
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_AUTO_DISABLED_CATEGORIES, $storeId);
    }

    /**
     * Get wildcard character
     */
    public function getWildcardCharacter(?int $storeId = null): string
    {
        return (string) Mage::getStoreConfig(self::XML_PATH_WILDCARD_CHARACTER, $storeId) ?: '*';
    }

    /**
     * Check if URL matching should be case sensitive
     */
    public function isCaseSensitive(?int $storeId = null): bool
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_CASE_SENSITIVE, $storeId);
    }

    /**
     * Check if query string should be stripped before matching
     */
    public function shouldStripQueryString(?int $storeId = null): bool
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_STRIP_QUERY_STRING, $storeId);
    }

    /**
     * Check if 404 logging is enabled
     */
    public function is404LoggingEnabled(?int $storeId = null): bool
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_404_LOGGING_ENABLED, $storeId);
    }

    /**
     * Check if bot traffic should be logged
     */
    public function shouldLogBots(?int $storeId = null): bool
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_404_LOG_BOTS, $storeId);
    }

    /**
     * Get maximum number of 404 log entries to keep
     */
    public function getMaxLogEntries(?int $storeId = null): int
    {
        return (int) Mage::getStoreConfig(self::XML_PATH_404_MAX_LOG_ENTRIES, $storeId);
    }

    /**
     * Check if product suggestions are enabled
     */
    public function isProductSuggestionsEnabled(?int $storeId = null): bool
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_SUGGESTIONS_ENABLED, $storeId);
    }

    /**
     * Get maximum number of product suggestions to show
     */
    public function getMaxSuggestions(?int $storeId = null): int
    {
        return (int) Mage::getStoreConfig(self::XML_PATH_SUGGESTIONS_MAX, $storeId) ?: 5;
    }

    /**
     * Check if Meilisearch should be used for suggestions
     */
    public function useMeilisearch(?int $storeId = null): bool
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_SUGGESTIONS_USE_MEILISEARCH, $storeId);
    }

    /**
     * Get CSV delimiter character
     */
    public function getCsvDelimiter(?int $storeId = null): string
    {
        return (string) Mage::getStoreConfig(self::XML_PATH_CSV_DELIMITER, $storeId) ?: ',';
    }

    /**
     * Get CSV enclosure character
     */
    public function getCsvEnclosure(?int $storeId = null): string
    {
        return (string) Mage::getStoreConfig(self::XML_PATH_CSV_ENCLOSURE, $storeId) ?: '"';
    }

    /**
     * Check if email notifications are enabled
     */
    public function isEmailNotificationsEnabled(?int $storeId = null): bool
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_EMAIL_ENABLED, $storeId);
    }

    /**
     * Get email notification frequency
     */
    public function getEmailFrequency(?int $storeId = null): string
    {
        return (string) Mage::getStoreConfig(self::XML_PATH_EMAIL_FREQUENCY, $storeId) ?: 'weekly';
    }

    /**
     * Get email notification recipient
     */
    public function getEmailRecipient(?int $storeId = null): string
    {
        return (string) Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT, $storeId);
    }

    /**
     * Get minimum hit count for email reports
     */
    public function getEmailMinimumHits(?int $storeId = null): int
    {
        return (int) Mage::getStoreConfig(self::XML_PATH_EMAIL_MINIMUM_HITS, $storeId) ?: 10;
    }

}
