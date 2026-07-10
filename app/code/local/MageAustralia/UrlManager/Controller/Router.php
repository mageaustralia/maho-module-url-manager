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
 * URL Manager Router
 *
 * Intercepts requests and handles redirects before they reach regular routing
 *
 * @category   Mageaus
 * @package    MageAustralia_UrlManager
 */
class MageAustralia_UrlManager_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract
{
    /**
     * Match URL and perform redirect if found
     *
     * This is called by Maho's front controller for each router in the chain.
     * Returning true stops the routing chain (redirect performed).
     * Returning false continues to the next router.
     */
    public function match(Mage_Core_Controller_Request_Http $request): bool
    {
        /** @var MageAustralia_UrlManager_Helper_Data $helper */
        $helper = Mage::helper('mageaustralia_urlmanager');

        // Check if redirect management is enabled
        if (!$helper->isEnabled()) {
            return false;
        }

        // Don't process admin requests
        if (Mage::app()->getStore()->isAdmin()) {
            return false;
        }

        // Get current request path
        $requestPath = trim($request->getPathInfo(), '/');

        // Build full URL for comparison (some redirects may use full URLs)
        $baseUrl = rtrim(Mage::getBaseUrl(), '/');
        $fullUrl = $baseUrl . '/' . $requestPath;

        Mage::log('URL Manager Router: Checking path: ' . $requestPath, Mage::LOG_DEBUG, 'mageaustralia_urlmanager.log');

        // Strip query string if configured
        if ($helper->shouldStripQueryString()) {
            // strtok('' , '?') returns false - coerce back to string or
            // strtolower() below throws a TypeError under strict_types
            // (the homepage request has an empty path).
            $requestPath = strtok($requestPath, '?') ?: '';
            $fullUrl = strtok($fullUrl, '?') ?: '';
        }

        // Case sensitivity handling
        $compareRequestPath = $requestPath;
        $compareFullUrl = $fullUrl;
        if (!$helper->isCaseSensitive()) {
            $compareRequestPath = strtolower($requestPath);
            $compareFullUrl = strtolower($fullUrl);
        }

        // Load all active redirects ordered by priority
        /** @var MageAustralia_UrlManager_Model_Resource_Redirect_Collection $redirects */
        $redirects = Mage::getResourceModel('mageaustralia_urlmanager/redirect_collection')
            ->addFieldToFilter('is_active', 1)
            ->setOrder('priority', 'DESC');

        foreach ($redirects as $redirect) {
            /** @var MageAustralia_UrlManager_Model_Redirect $redirect */
            $sourceUrl = trim((string) $redirect->getSourceUrl(), '/');

            // Host-agnostic candidate: when the stored source is a FULL URL
            // (virtually all imported rows are anchored to the production
            // host), also match its path component - otherwise the whole
            // table is dead on any other environment host (dev/staging) and
            // untestable before cutover. Additive only: the original
            // full-URL comparison still runs, so production behaviour is
            // unchanged.
            $candidates = [$sourceUrl];
            if (str_starts_with($sourceUrl, 'http://') || str_starts_with($sourceUrl, 'https://')) {
                $sourcePath = trim((string) (parse_url($sourceUrl, PHP_URL_PATH) ?: ''), '/');
                if ($sourcePath !== '') {
                    $candidates[] = $sourcePath;
                }
            }

            // Case sensitivity handling for source
            if (!$helper->isCaseSensitive()) {
                $candidates = array_map('strtolower', $candidates);
            }

            // Check for match (try both request path and full URL)
            $isMatch = false;

            foreach ($candidates as $compareSourceUrl) {
                if ($redirect->getIsWildcard()) {
                    // Wildcard matching
                    $pattern = $helper->buildWildcardPattern($compareSourceUrl);
                    $isMatch = preg_match('/^' . $pattern . '$/', $compareRequestPath) ||
                               preg_match('/^' . $pattern . '$/', $compareFullUrl);
                } else {
                    // Exact match (try both path and full URL)
                    $isMatch = ($compareSourceUrl === $compareRequestPath) ||
                              ($compareSourceUrl === $compareFullUrl);
                }
                if ($isMatch) {
                    break;
                }
            }

            if ($isMatch) {
                Mage::log(sprintf(
                    'URL Manager Router: MATCH! Redirect #%d: %s => %s (status: %d)',
                    $redirect->getId(),
                    $sourceUrl,
                    $redirect->getDestinationUrl(),
                    $redirect->getStatusCode(),
                ), Mage::LOG_INFO, 'mageaustralia_urlmanager.log');

                // Update hit statistics
                $redirect->setHitCount($redirect->getHitCount() + 1);
                $redirect->setLastHitAt(Mage_Core_Model_Locale::nowUtc());
                $redirect->save();

                // Perform redirect - relative destinations and internal hosts both
                // resolve onto whichever store is serving this request
                $destinationUrl = $helper->resolveDestinationUrl((string) $redirect->getDestinationUrl());

                // Send redirect response
                $response = Mage::app()->getResponse();
                $response->setRedirect($destinationUrl, $redirect->getStatusCode());
                $response->sendResponse();
                exit;
            }
        }

        // No redirect found, continue to next router
        return false;
    }
}
