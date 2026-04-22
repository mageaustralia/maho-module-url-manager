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
 * URL Manager Router
 *
 * Intercepts requests and handles redirects before they reach regular routing
 *
 * @category   Mageaus
 * @package    Mageaus_UrlManager
 */
class Mageaus_UrlManager_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract
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
        /** @var Mageaus_UrlManager_Helper_Data $helper */
        $helper = Mage::helper('mageaus_urlmanager');

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

        Mage::log('URL Manager Router: Checking path: ' . $requestPath, Mage::LOG_DEBUG, 'mageaus_urlmanager.log');

        // Strip query string if configured
        if ($helper->shouldStripQueryString()) {
            $requestPath = strtok($requestPath, '?');
            $fullUrl = strtok($fullUrl, '?');
        }

        // Case sensitivity handling
        $compareRequestPath = $requestPath;
        $compareFullUrl = $fullUrl;
        if (!$helper->isCaseSensitive()) {
            $compareRequestPath = strtolower($requestPath);
            $compareFullUrl = strtolower($fullUrl);
        }

        // Load all active redirects ordered by priority
        /** @var Mageaus_UrlManager_Model_Resource_Redirect_Collection $redirects */
        $redirects = Mage::getResourceModel('mageaus_urlmanager/redirect_collection')
            ->addFieldToFilter('is_active', 1)
            ->setOrder('priority', 'DESC');

        foreach ($redirects as $redirect) {
            /** @var Mageaus_UrlManager_Model_Redirect $redirect */
            $sourceUrl = trim((string) $redirect->getSourceUrl(), '/');

            // Case sensitivity handling for source
            $compareSourceUrl = $sourceUrl;
            if (!$helper->isCaseSensitive()) {
                $compareSourceUrl = strtolower($sourceUrl);
            }

            // Check for match (try both request path and full URL)
            $isMatch = false;

            if ($redirect->getIsWildcard()) {
                // Wildcard matching
                $pattern = str_replace(
                    $helper->getWildcardCharacter(),
                    '.*',
                    preg_quote($compareSourceUrl, '/'),
                );
                $isMatch = preg_match('/^' . $pattern . '$/', $compareRequestPath) ||
                           preg_match('/^' . $pattern . '$/', $compareFullUrl);
            } else {
                // Exact match (try both path and full URL)
                $isMatch = ($compareSourceUrl === $compareRequestPath) ||
                          ($compareSourceUrl === $compareFullUrl);
            }

            if ($isMatch) {
                Mage::log(sprintf(
                    'URL Manager Router: MATCH! Redirect #%d: %s => %s (status: %d)',
                    $redirect->getId(),
                    $sourceUrl,
                    $redirect->getDestinationUrl(),
                    $redirect->getStatusCode(),
                ), Mage::LOG_INFO, 'mageaus_urlmanager.log');

                // Update hit statistics
                $redirect->setHitCount($redirect->getHitCount() + 1);
                $redirect->setLastHitAt(Mage_Core_Model_Locale::now());
                $redirect->save();

                // Perform redirect
                $destinationUrl = $redirect->getDestinationUrl();

                // Handle relative URLs
                if (!preg_match('/^https?:\/\//', (string) $destinationUrl)) {
                    $destinationUrl = Mage::getBaseUrl() . ltrim((string) $destinationUrl, '/');
                }

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
