<?php

/**
 * Maho
 *
 * @category   Mageaus
 * @package    Mageaus_UrlManager
 * @copyright  Copyright (c) 2025 Maho (https://mahocommerce.com)
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 */

/**
 * URL Manager Observer
 *
 * Handles redirect processing and 404 logging
 *
 * @category   Mageaus
 * @package    Mageaus_UrlManager
 */
class Mageaus_UrlManager_Model_Observer
{
    /**
     * Register custom router for handling redirects
     *
     * This is called via controller_front_init_routers event
     */
    public function initRouters(Varien_Event_Observer $observer): void
    {
        Mage::log('URL Manager Observer: initRouters() called', Mage::LOG_INFO, 'mageaus_urlmanager.log');

        $router = new Mageaus_UrlManager_Controller_Router();
        $observer->getEvent()->getFront()->addRouter('mageaus_urlmanager', $router);

        Mage::log('URL Manager Observer: Router added successfully', Mage::LOG_INFO, 'mageaus_urlmanager.log');
    }

    /**
     * Handle redirects before controller dispatch
     */
    public function handleRedirects(Varien_Event_Observer $observer): void
    {
        /** @var Mageaus_UrlManager_Helper_Data $helper */
        $helper = Mage::helper('mageaus_urlmanager');

        Mage::log('URL Manager Observer: handleRedirects() called', Mage::LOG_DEBUG, 'mageaus_urlmanager.log');

        // Check if redirect management is enabled
        if (!$helper->isEnabled()) {
            Mage::log('URL Manager Observer: Module is disabled', Mage::LOG_DEBUG, 'mageaus_urlmanager.log');
            return;
        }

        // Get current request
        $request = $observer->getEvent()->getControllerAction()->getRequest();
        $requestPath = trim((string) $request->getRequestUri(), '/');

        // Build full URL for comparison (some redirects may use full URLs)
        $baseUrl = rtrim(Mage::getBaseUrl(), '/');
        $fullUrl = $baseUrl . '/' . $requestPath;

        Mage::log('URL Manager Observer: Request URI: ' . $requestPath, Mage::LOG_INFO, 'mageaus_urlmanager.log');
        Mage::log('URL Manager Observer: Full URL: ' . $fullUrl, Mage::LOG_DEBUG, 'mageaus_urlmanager.log');

        // Strip query string if configured
        if ($helper->shouldStripQueryString()) {
            $requestPath = strtok($requestPath, '?');
            $fullUrl = strtok($fullUrl, '?');
        }

        // Case sensitivity handling
        if (!$helper->isCaseSensitive()) {
            $requestPath = strtolower($requestPath);
            $fullUrl = strtolower($fullUrl);
        }

        // Load all active redirects ordered by priority
        /** @var Mageaus_UrlManager_Model_Resource_Redirect_Collection $redirects */
        $redirects = Mage::getResourceModel('mageaus_urlmanager/redirect_collection')
            ->addFieldToFilter('is_active', 1)
            ->setOrder('priority', 'DESC');

        foreach ($redirects as $redirect) {
            /** @var Mageaus_UrlManager_Model_Redirect $redirect */
            $sourceUrl = trim((string) $redirect->getSourceUrl(), '/');

            // Case sensitivity handling
            if (!$helper->isCaseSensitive()) {
                $sourceUrl = strtolower($sourceUrl);
            }

            // Check for match (try both request path and full URL)
            $isMatch = false;

            if ($redirect->getIsWildcard()) {
                // Wildcard matching
                $pattern = str_replace(
                    $helper->getWildcardCharacter(),
                    '.*',
                    preg_quote($sourceUrl, '/'),
                );
                $isMatch = preg_match('/^' . $pattern . '$/', $requestPath) ||
                           preg_match('/^' . $pattern . '$/', $fullUrl);
            } else {
                // Exact match (try both path and full URL)
                $isMatch = ($sourceUrl === $requestPath) || ($sourceUrl === $fullUrl);
            }

            Mage::log(sprintf(
                'URL Manager Observer: Checking redirect #%d: %s (wildcard: %s) against %s / %s = %s',
                $redirect->getId(),
                $sourceUrl,
                $redirect->getIsWildcard() ? 'yes' : 'no',
                $requestPath,
                $fullUrl,
                $isMatch ? 'MATCH' : 'no match',
            ), Mage::LOG_DEBUG, 'mageaus_urlmanager.log');

            if ($isMatch) {
                // Update hit statistics
                $redirect->setHitCount($redirect->getHitCount() + 1);
                $redirect->setLastHitAt(Mage_Core_Model_Locale::nowUtc());
                $redirect->save();

                // Perform redirect
                $destinationUrl = $redirect->getDestinationUrl();

                // Handle relative URLs
                if (!preg_match('/^https?:\/\//', (string) $destinationUrl)) {
                    $destinationUrl = Mage::getBaseUrl() . ltrim((string) $destinationUrl, '/');
                }

                // Log redirect for debugging
                Mage::log(sprintf(
                    'URL Manager: Redirecting %s to %s (Status: %d, Priority: %d)',
                    $requestPath,
                    $destinationUrl,
                    $redirect->getStatusCode(),
                    $redirect->getPriority(),
                ), Mage::LOG_INFO);

                // Send redirect response
                $response = Mage::app()->getResponse();
                $response->setRedirect($destinationUrl, $redirect->getStatusCode());
                $response->sendResponse();
                exit;
            }
        }
    }

    /**
     * Log 404 errors and attempt fuzzy matching
     */
    public function logNotFound(Varien_Event_Observer $observer): void
    {
        /** @var Mageaus_UrlManager_Helper_Data $helper */
        $helper = Mage::helper('mageaus_urlmanager');

        // Check if 404 logging is enabled
        if (!$helper->is404LoggingEnabled()) {
            return;
        }

        // Check if this is a 404 response by checking headers
        $response = Mage::app()->getResponse();
        $is404 = false;
        $headers = $response->getHeaders();

        foreach ($headers as $header) {
            if (strtolower((string) $header['name']) === 'status') {
                if (str_contains((string) $header['value'], '404')) {
                    $is404 = true;
                    break;
                }
            }
        }

        if (!$is404) {
            return;
        }

        $request = Mage::app()->getRequest();
        $requestPath = trim((string) $request->getRequestUri(), '/');

        // Check if we should log bot traffic
        $userAgent = $request->getHeader('User-Agent');
        if (!$helper->shouldLogBots() && $this->isBot($userAgent)) {
            return;
        }

        // Get or create log entry
        /** @var Mageaus_UrlManager_Model_Resource_Notfoundlog_Collection $collection */
        $collection = Mage::getResourceModel('mageaus_urlmanager/notfoundlog_collection')
            ->addFieldToFilter('request_url', $requestPath)
            ->addFieldToFilter('store_id', Mage::app()->getStore()->getId());

        if ($collection->getSize() > 0) {
            // Update existing entry
            /** @var Mageaus_UrlManager_Model_Notfoundlog $log */
            $log = $collection->getFirstItem();
            $log->setHitCount($log->getHitCount() + 1);
            $log->setLastHitAt(Mage_Core_Model_Locale::nowUtc());
        } else {
            // Create new entry
            $log = Mage::getModel('mageaus_urlmanager/notfoundlog');
            $log->setData([
                'request_url' => $requestPath,
                'referer_url' => $request->getHeader('Referer'),
                'user_agent' => $userAgent,
                'ip_address' => $request->getClientIp(),
                'store_id' => Mage::app()->getStore()->getId(),
                'hit_count' => 1,
                'last_hit_at' => Mage_Core_Model_Locale::nowUtc(),
            ]);

            // Try to find suggested product using fuzzy matching
            if ($helper->isProductSuggestionsEnabled()) {
                $suggestedProductId = $this->findSuggestedProduct($requestPath);
                if ($suggestedProductId) {
                    $log->setSuggestedProductId($suggestedProductId);
                }
            }
        }

        try {
            $log->save();

            // Check if we need to clean up old entries
            $this->cleanupOldLogs();
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Detect if user agent is a bot/crawler
     */
    protected function isBot(string $userAgent): bool
    {
        $botPatterns = [
            'bot', 'crawler', 'spider', 'scraper', 'slurp', 'crawl',
            'google', 'bing', 'yahoo', 'baidu', 'yandex', 'duckduck',
            'facebook', 'twitter', 'linkedin', 'pinterest',
            'wget', 'curl', 'python', 'java', 'httpclient',
        ];

        $userAgentLower = strtolower($userAgent);
        foreach ($botPatterns as $pattern) {
            if (str_contains($userAgentLower, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find suggested product using fuzzy matching
     */
    protected function findSuggestedProduct(string $requestPath): ?int
    {
        /** @var Mageaus_UrlManager_Helper_Data $helper */
        $helper = Mage::helper('mageaus_urlmanager');

        // Use Meilisearch if enabled and available
        if ($helper->useMeilisearch() && class_exists('MeiliSearch\Client')) {
            try {
                return $this->findProductWithMeilisearch($requestPath);
            } catch (Exception $e) {
                Mage::log('Meilisearch product suggestion failed: ' . $e->getMessage(), Mage::LOG_WARNING);
            }
        }

        // Fall back to basic fuzzy matching
        return $this->findProductWithBasicFuzzy($requestPath);
    }

    /**
     * Find product using Meilisearch
     */
    protected function findProductWithMeilisearch(string $requestPath): ?int
    {
        // Extract potential product keywords from URL
        $keywords = $this->extractKeywords($requestPath);

        // TODO: Implement Meilisearch integration
        // This requires Meilisearch to be configured and product index to exist

        return null;
    }

    /**
     * Find product using basic fuzzy matching
     */
    protected function findProductWithBasicFuzzy(string $requestPath): ?int
    {
        // Extract potential product keywords from URL
        $keywords = $this->extractKeywords($requestPath);

        if (empty($keywords)) {
            return null;
        }

        // Search for products with similar names or SKUs
        /** @var Mage_Catalog_Model_Resource_Product_Collection $products */
        $products = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->setPageSize(1);

        // Add keyword filters
        $nameFilters = [];
        foreach ($keywords as $keyword) {
            $nameFilters[] = ['like' => '%' . $keyword . '%'];
        }

        $products->addAttributeToFilter('name', $nameFilters);

        if ($products->getSize() > 0) {
            return $products->getFirstItem()->getId();
        }

        return null;
    }

    /**
     * Extract keywords from URL path
     */
    protected function extractKeywords(string $path): array
    {
        // Remove common URL patterns
        $path = preg_replace('/\.(html|htm|php)$/', '', $path);

        // Split by common separators
        $parts = preg_split('/[\/\-_\.]/', (string) $path);

        // Filter out common words and short strings
        $stopWords = ['the', 'and', 'or', 'of', 'to', 'in', 'for', 'a', 'an'];
        $keywords = [];

        foreach ($parts as $part) {
            $part = strtolower(trim($part));
            if (strlen($part) > 2 && !in_array($part, $stopWords)) {
                $keywords[] = $part;
            }
        }

        return $keywords;
    }

    /**
     * Clean up old 404 log entries
     */
    protected function cleanupOldLogs(): void
    {
        /** @var Mageaus_UrlManager_Helper_Data $helper */
        $helper = Mage::helper('mageaus_urlmanager');

        $maxEntries = $helper->getMaxLogEntries();

        if ($maxEntries <= 0) {
            return; // Unlimited
        }

        /** @var Mageaus_UrlManager_Model_Resource_Notfoundlog_Collection $collection */
        $collection = Mage::getResourceModel('mageaus_urlmanager/notfoundlog_collection');

        if ($collection->getSize() > $maxEntries) {
            // Delete oldest entries (keep only max entries)
            $idsToDelete = $collection
                ->setOrder('last_hit_at', 'ASC')
                ->setPageSize($collection->getSize() - $maxEntries)
                ->getColumnValues('notfound_log_id');

            if (!empty($idsToDelete)) {
                Mage::getResourceModel('mageaus_urlmanager/notfoundlog')
                    ->getConnection()
                    ->delete(
                        Mage::getResourceModel('mageaus_urlmanager/notfoundlog')->getMainTable(),
                        ['notfound_log_id IN (?)' => $idsToDelete],
                    );
            }
        }
    }

    /**
     * Clear 404 logs that match the newly created redirect
     */
    public function clearMatchingNotFoundLogs(Varien_Event_Observer $observer): void
    {
        /** @var Mageaus_UrlManager_Model_Redirect $redirect */
        $redirect = $observer->getEvent()->getRedirect();

        if (!$redirect || !$redirect->getId()) {
            return;
        }

        $sourceUrl = trim((string) $redirect->getSourceUrl(), '/');

        /** @var Mageaus_UrlManager_Helper_Data $helper */
        $helper = Mage::helper('mageaus_urlmanager');

        // Get all 404 logs that match this redirect
        /** @var Mageaus_UrlManager_Model_Resource_Notfoundlog_Collection $collection */
        $collection = Mage::getResourceModel('mageaus_urlmanager/notfoundlog_collection');

        if ($redirect->getIsWildcard()) {
            // For wildcard redirects, match using SQL LIKE
            $pattern = str_replace($helper->getWildcardCharacter(), '%', $sourceUrl);
            $collection->addFieldToFilter('request_url', ['like' => $pattern]);
        } else {
            // For exact match redirects
            if (!$helper->isCaseSensitive()) {
                // Case-insensitive match - need to use SQL
                $collection->getSelect()->where('LOWER(request_url) = ?', strtolower($sourceUrl));
            } else {
                $collection->addFieldToFilter('request_url', $sourceUrl);
            }
        }

        // Delete matching 404 logs
        $deleted = 0;
        foreach ($collection as $log) {
            try {
                $log->delete();
                $deleted++;
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        if ($deleted > 0) {
            Mage::log(
                sprintf('Cleared %d 404 log entries matching redirect: %s', $deleted, $sourceUrl),
                Mage::LOG_INFO,
                'mageaus_urlmanager.log',
            );
        }
    }

    /**
     * Send daily 404 report
     */
    public function sendDailyReport(): void
    {
        /** @var Mageaus_UrlManager_Helper_Data $helper */
        $helper = Mage::helper('mageaus_urlmanager');

        if (!$helper->isEmailNotificationsEnabled()) {
            return;
        }

        if ($helper->getEmailFrequency() !== 'daily') {
            return;
        }

        $this->sendReport('Daily');
    }

    /**
     * Send weekly 404 report
     */
    public function sendWeeklyReport(): void
    {
        /** @var Mageaus_UrlManager_Helper_Data $helper */
        $helper = Mage::helper('mageaus_urlmanager');

        if (!$helper->isEmailNotificationsEnabled()) {
            return;
        }

        if ($helper->getEmailFrequency() !== 'weekly') {
            return;
        }

        $this->sendReport('Weekly');
    }

    /**
     * Send 404 report email
     */
    protected function sendReport(string $period): void
    {
        /** @var Mageaus_UrlManager_Helper_Data $helper */
        $helper = Mage::helper('mageaus_urlmanager');

        $recipientEmail = $helper->getEmailRecipient();
        if (empty($recipientEmail)) {
            Mage::log('Cannot send 404 report: No recipient email configured', Mage::LOG_WARNING, 'mageaus_urlmanager.log');
            return;
        }

        $minimumHits = $helper->getEmailMinimumHits();

        // Only include 404s that have been hit since the previous report — stops
        // the email re-listing the same lifetime top-404s every run, and acts as
        // idempotent defence-in-depth against duplicate cron scheduling.
        /** @var Mageaus_UrlManager_Model_Resource_Notfoundlog_Collection $collection */
        $collection = Mage::getResourceModel('mageaus_urlmanager/notfoundlog_collection')
            ->addFieldToFilter('hit_count', ['gteq' => $minimumHits])
            ->setOrder('hit_count', 'DESC')
            ->setPageSize(50);

        $collection->getSelect()->where(
            '(last_reported_at IS NULL OR last_hit_at > last_reported_at)',
        );

        $top404s = [];
        $reportedIds = [];
        foreach ($collection as $log) {
            $top404s[] = [
                'request_url' => $log->getRequestUrl(),
                'hit_count' => $log->getHitCount(),
                'last_hit_at' => $log->getLastHitAt(),
            ];
            $reportedIds[] = (int) $log->getId();
        }

        if (empty($top404s)) {
            Mage::log(sprintf('No new 404s since last %s report — skipping email', $period), Mage::LOG_INFO, 'mageaus_urlmanager.log');
            return;
        }

        // Send email
        try {
            $translate = Mage::getSingleton('core/translate');
            $translate->setTranslateInline(false);

            $storeId = Mage::app()->getStore()->getId();

            $emailTemplate = Mage::getModel('core/email_template');
            $emailTemplate->setDesignConfig(['area' => 'frontend', 'store' => $storeId]);

            // Load the registered template (config.xml -> global/template/email).
            // The subject comes from the template's @subject directive and is
            // processed with the vars passed to send(), so it is no longer
            // hard-coded here and respects store-scoped/admin-edited overrides.
            $emailTemplate->loadDefault('mageaus_urlmanager_404_report');

            // Set sender from general identity
            $senderName = Mage::getStoreConfig('trans_email/ident_general/name', $storeId);
            $senderEmail = Mage::getStoreConfig('trans_email/ident_general/email', $storeId);
            $emailTemplate->setSenderName($senderName);
            $emailTemplate->setSenderEmail($senderEmail);

            $variables = [
                'period' => $period,
                'total_404s' => count($top404s),
                'top_404s' => $top404s,
                'store_name' => Mage::app()->getStore()->getName(),
                'admin_url' => Mage::helper('adminhtml')->getUrl('adminhtml/redirect/notfoundlog'),
            ];

            $sent = $emailTemplate->send(
                $recipientEmail,
                null,
                $variables,
            );

            if (!$sent) {
                Mage::log('Failed to send 404 report email', Mage::LOG_ERROR, 'mageaus_urlmanager.log');
            } else {
                // Stamp only AFTER email is sent successfully; a failed send leaves
                // rows eligible for the next run so we do not silently drop them.
                if (!empty($reportedIds)) {
                    $writeAdapter = Mage::getSingleton('core/resource')->getConnection('core_write');
                    $writeAdapter->update(
                        Mage::getSingleton('core/resource')->getTableName('mageaus_urlmanager/notfoundlog'),
                        ['last_reported_at' => Mage_Core_Model_Locale::nowUtc()],
                        ['notfound_log_id IN (?)' => $reportedIds],
                    );
                }
                Mage::log(
                    sprintf('Sent %s 404 report to %s (%d URLs)', $period, $recipientEmail, count($top404s)),
                    Mage::LOG_INFO,
                    'mageaus_urlmanager.log',
                );
            }

            $translate->setTranslateInline(true);
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }
}
