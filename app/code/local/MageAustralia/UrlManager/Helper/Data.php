<?php

/**
 * Maho
 *
 * @category   MageAustralia
 * @package    MageAustralia_UrlManager
 * @copyright  Copyright (c) 2025 Maho (https://mahocommerce.com) & MageAustralia (https://mageaustralia.com.au)
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 */

declare(strict_types=1);

/**
 * UrlManager Data Helper
 *
 * Provides configuration access methods
 *
 * @category   MageAustralia
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
    public const XML_PATH_404_IGNORE_PATTERNS = 'mageaustralia_urlmanager/logging/ignore_patterns';

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
     * URL substrings excluded from 404 logging, one per line.
     *
     * @return string[]
     */
    public function getIgnorePatterns(?int $storeId = null): array
    {
        $configured = (string) Mage::getStoreConfig(self::XML_PATH_404_IGNORE_PATTERNS, $storeId);

        if ($configured === '') {
            return [];
        }

        $patterns = preg_split('/[\r\n]+/', $configured, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_filter(array_map('trim', $patterns), static fn(string $p): bool => $p !== ''));
    }

    /**
     * Check whether a request path matches any configured ignore pattern
     * (case-insensitive substring match).
     */
    public function shouldIgnoreUrl(string $url, ?int $storeId = null): bool
    {
        foreach ($this->getIgnorePatterns($storeId) as $pattern) {
            if (stripos($url, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Ad/analytics click identifiers that carry no diagnostic value for a 404.
     * Prefixes end with '_' and match any parameter starting with them.
     */
    public const TRACKING_PARAMS = [
        'fbclid', 'gclid', 'gbraid', 'wbraid', 'dclid', 'msclkid', 'twclid',
        'ttclid', 'igshid', 'yclid', 'epik', 'srsltid', 'mc_cid', 'mc_eid',
        '_branch_match_id',
    ];

    public const TRACKING_PARAM_PREFIXES = ['utm_'];

    /**
     * Strip ad/analytics click IDs from a URL's query string.
     *
     * The same broken path arriving with a different fbclid each time would
     * otherwise create a new log row per visit, splintering hit_count so a
     * high-traffic 404 reads as a scatter of one-hit entries. Genuine query
     * parameters are preserved - a broken '?p=2' is a real thing to fix.
     */
    public function stripTrackingParams(string $url): string
    {
        if (!str_contains($url, '?')) {
            return $url;
        }

        [$path, $query] = explode('?', $url, 2);
        parse_str($query, $params);

        foreach (array_keys($params) as $key) {
            $key = (string) $key;
            if (in_array(strtolower($key), self::TRACKING_PARAMS, true)) {
                unset($params[$key]);
                continue;
            }
            foreach (self::TRACKING_PARAM_PREFIXES as $prefix) {
                if (str_starts_with(strtolower($key), $prefix)) {
                    unset($params[$key]);
                    break;
                }
            }
        }

        $rebuilt = http_build_query($params);

        return $rebuilt === '' ? $path : $path . '?' . $rebuilt;
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

    /**
     * Confidence that a 404 path is a real store URL rather than a probe.
     *
     * The 404 report exists to surface catalog URLs that lost their redirect.
     * Ranking by hit_count alone can never do that: a vulnerability scanner
     * hits one path 20+ times while a customer following a dead product link
     * hits it once, so scanners take every slot. Worse, they also consumed the
     * whole max_log_entries budget, evicting real 404s within days.
     *
     * Classifying on the way in fixes both: the report filters to CONFIDENT,
     * and cleanup evicts the low tiers first so real 404s survive.
     *
     * Deliberately an allowlist. Scanners invent new paths constantly, so a
     * blocklist needs endless maintenance; the shape of OUR urls does not
     * change.
     */
    public const CATALOG_CONFIDENCE_PROBE = 0;
    public const CATALOG_CONFIDENCE_POSSIBLE = 1;
    public const CATALOG_CONFIDENCE_CONFIDENT = 2;

    /**
     * First path segments that are never a catalog URL. Only needed to keep
     * asset paths (js/blank.html) out of the CONFIDENT tier - the suffix and
     * route tests below already exclude the bulk of probe traffic.
     */
    public const NON_CATALOG_PREFIXES = [
        'js', 'css', 'media', 'skin', 'static', 'assets', 'api', 'rest',
        'graphql', 'admin', 'administrator', 'plugins', 'modules', 'vendor',
        'libraries', 'templates', 'uploads', 'upload', 'images', 'cgi-bin',
        'wordpress', 'sitecore', 'actuator', 'solr', 'phpmyadmin', 'typo3',
        'joomla', 'bitrix', 'laravel', 'telescope', 'console', 'webinterface',
        'endpoints', '_ignition', '_profiler', '__debug__', '.git', '.env',
        '.aws', '.well-known', 'server-status', 'server-info', 'webdav',
    ];

    /**
     * Classify a logged 404 path. Returns one of the CATALOG_CONFIDENCE_* values.
     */
    public function getCatalogConfidence(string $url, ?int $storeId = null): int
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            $path = $url;
        }
        $path = strtolower(trim($path, '/'));

        if ($path === '' || str_contains($path, 'index.php')) {
            return self::CATALOG_CONFIDENCE_PROBE;
        }

        $segments = explode('/', $path);
        $first = $segments[0];

        if (str_starts_with($first, 'wp-') || in_array($first, self::NON_CATALOG_PREFIXES, true)) {
            return self::CATALOG_CONFIDENCE_PROBE;
        }

        // Internal catalog route - the strongest signal there is, and it hands
        // us the entity id directly rather than a slug to go looking for.
        if (preg_match('#(^|/)catalog/(product|category)/view(/|$)#', $path) === 1) {
            return self::CATALOG_CONFIDENCE_CONFIDENT;
        }

        $last = $segments[count($segments) - 1];

        // Catalog URL suffix. Tested BEFORE the extension rejection below,
        // since .html is itself an extension.
        foreach ($this->getCatalogUrlSuffixes($storeId) as $suffix) {
            if ($suffix !== '' && str_ends_with($last, $suffix)) {
                return self::CATALOG_CONFIDENCE_CONFIDENT;
            }
        }

        // Any other file extension is a probe (.php7, .jsp, .do, .xml, ...).
        if (preg_match('/\.[a-z0-9]{1,7}$/', $last) === 1) {
            return self::CATALOG_CONFIDENCE_PROBE;
        }

        // Extensionless and not stop-listed. Could be a suffix-less category
        // or a CMS page, could be a probe - kept out of the email, visible in
        // the admin grid.
        return self::CATALOG_CONFIDENCE_POSSIBLE;
    }

    /**
     * Configured product/category URL suffixes, normalised to include the dot.
     *
     * @return string[]
     */
    public function getCatalogUrlSuffixes(?int $storeId = null): array
    {
        $suffixes = [];
        foreach (['catalog/seo/product_url_suffix', 'catalog/seo/category_url_suffix'] as $path) {
            $suffix = trim((string) Mage::getStoreConfig($path, $storeId));
            if ($suffix === '') {
                continue;
            }
            $suffixes[] = str_starts_with($suffix, '.') ? strtolower($suffix) : '.' . strtolower($suffix);
        }

        return array_values(array_unique($suffixes)) ?: ['.html'];
    }

}
