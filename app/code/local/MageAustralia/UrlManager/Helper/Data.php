<?php

/**
 * Maho
 *
 * @category   Mageaus
 * @package    MageAustralia_UrlManager
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
 * @package    MageAustralia_UrlManager
 */
class MageAustralia_UrlManager_Helper_Data extends Mage_Core_Helper_Abstract
{
    // Configuration paths
    public const XML_PATH_GENERAL_ENABLED = 'mageaustralia_urlmanager/redirects/enabled';
    public const XML_PATH_WILDCARD_CHARACTER = 'mageaustralia_urlmanager/redirects/wildcard_character';
    public const XML_PATH_CASE_SENSITIVE = 'mageaustralia_urlmanager/redirects/case_sensitive';
    public const XML_PATH_STRIP_QUERY_STRING = 'mageaustralia_urlmanager/redirects/strip_query_string';
    public const XML_PATH_INTERNAL_HOSTS = 'mageaustralia_urlmanager/redirects/internal_hosts';

    public const XML_PATH_404_LOGGING_ENABLED = 'mageaustralia_urlmanager/logging/enabled';
    public const XML_PATH_404_LOG_BOTS = 'mageaustralia_urlmanager/logging/log_bots';
    public const XML_PATH_404_MAX_LOG_ENTRIES = 'mageaustralia_urlmanager/logging/max_log_entries';

    public const XML_PATH_SUGGESTIONS_ENABLED = 'mageaustralia_urlmanager/suggestions/enabled';
    public const XML_PATH_SUGGESTIONS_MAX = 'mageaustralia_urlmanager/suggestions/max_suggestions';
    public const XML_PATH_SUGGESTIONS_USE_MEILISEARCH = 'mageaustralia_urlmanager/suggestions/use_meilisearch';

    public const XML_PATH_AUTO_DISABLED_PRODUCTS = 'mageaustralia_urlmanager/auto_redirects/disabled_products';
    public const XML_PATH_AUTO_NOT_VISIBLE_PRODUCTS = 'mageaustralia_urlmanager/auto_redirects/not_visible_products';
    public const XML_PATH_AUTO_DISABLED_CATEGORIES = 'mageaustralia_urlmanager/auto_redirects/disabled_categories';

    public const XML_PATH_CSV_DELIMITER = 'mageaustralia_urlmanager/csv/delimiter';
    public const XML_PATH_CSV_ENCLOSURE = 'mageaustralia_urlmanager/csv/enclosure';
    public const XML_PATH_CSV_SKIP_DUPLICATES = 'mageaustralia_urlmanager/csv/skip_duplicates';

    public const XML_PATH_EMAIL_ENABLED = 'mageaustralia_urlmanager/email_notifications/enabled';
    public const XML_PATH_EMAIL_FREQUENCY = 'mageaustralia_urlmanager/email_notifications/frequency';
    public const XML_PATH_EMAIL_RECIPIENT = 'mageaustralia_urlmanager/email_notifications/recipient_email';
    public const XML_PATH_EMAIL_MINIMUM_HITS = 'mageaustralia_urlmanager/email_notifications/minimum_hits';

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
     * Get the configured disabled-product redirect type
     */
    public function getDisabledProductsRedirectType(?int $storeId = null): string
    {
        return (string) Mage::getStoreConfig(self::XML_PATH_AUTO_DISABLED_PRODUCTS, $storeId);
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
     * Hosts that belong to this shop, regardless of which environment it runs in.
     *
     * @return string[] lowercase hostnames
     */
    public function getInternalHosts(?int $storeId = null): array
    {
        $configured = (string) Mage::getStoreConfig(self::XML_PATH_INTERNAL_HOSTS, $storeId);

        $hosts = array_map(
            static fn($host): string => strtolower(trim((string) $host)),
            explode(',', $configured),
        );

        return array_values(array_filter($hosts, static fn(string $host): bool => $host !== ''));
    }

    /**
     * Turn a stored destination into a URL on the current store.
     *
     * Redirect lists are usually exported from production, so their destinations are
     * absolute production URLs. Replaying that list on a staging or development copy
     * would bounce visitors onto production, which makes the redirects untestable.
     * Configure those production hostnames as internal hosts and the path is re-hosted
     * onto whichever store is currently serving the request. Any other host is left
     * alone, so genuine off-site redirects still work.
     */
    public function resolveDestinationUrl(string $destinationUrl, ?int $storeId = null): string
    {
        $destinationUrl = trim($destinationUrl);

        if (!preg_match('#^https?://#i', $destinationUrl)) {
            return Mage::getBaseUrl() . ltrim($destinationUrl, '/');
        }

        $parsed = parse_url($destinationUrl);
        $host = isset($parsed['host']) ? strtolower($parsed['host']) : '';

        if ($host === '' || !in_array($host, $this->getInternalHosts($storeId), true)) {
            return $destinationUrl;
        }

        $path = $parsed['path'] ?? '/';
        $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';
        $fragment = isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '';

        return Mage::getBaseUrl() . ltrim($path, '/') . $query . $fragment;
    }

    /**
     * Build a regex that matches a source URL containing wildcard characters.
     *
     * The wildcard has to be swapped in after quoting: preg_quote() escapes the
     * wildcard itself, so replacing it beforehand leaves an escaped literal behind
     * and the pattern silently stops matching anything.
     */
    public function buildWildcardPattern(string $sourceUrl, ?int $storeId = null): string
    {
        $wildcardChar = $this->getWildcardCharacter($storeId);

        return str_replace(
            preg_quote($wildcardChar, '/'),
            '.*',
            preg_quote($sourceUrl, '/'),
        );
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
