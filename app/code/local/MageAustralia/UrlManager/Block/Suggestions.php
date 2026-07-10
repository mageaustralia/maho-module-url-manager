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
 * Product Suggestions Block for 404 Pages
 *
 * @category   MageAustralia
 * @package    MageAustralia_UrlManager
 */
class MageAustralia_UrlManager_Block_Suggestions extends Mage_Core_Block_Template
{
    /**
     * Get suggested products based on 404 URL
     */
    public function getSuggestedProducts(): ?Mage_Catalog_Model_Resource_Product_Collection
    {
        try {
            Mage::log('UrlManager Suggestions Block: getSuggestedProducts() called', null, 'urlmanager_debug.log');

            /** @var MageAustralia_UrlManager_Helper_Data $helper */
            $helper = Mage::helper('mageaustralia_urlmanager');

            $enabled = $helper->isProductSuggestionsEnabled();
            Mage::log('Product suggestions enabled: ' . ($enabled ? 'YES' : 'NO'), null, 'urlmanager_debug.log');

            if (!$enabled) {
                return null;
            }

            // Get the requested URL path
            $requestUri = Mage::app()->getRequest()->getRequestUri();
            $searchQuery = $this->extractSearchTermFromUrl($requestUri);

            if (empty($searchQuery)) {
                return $this->getFallbackProducts();
            }

            // Try Meilisearch first if enabled
            if ($helper->useMeilisearch()) {
                try {
                    $products = $this->searchWithMeilisearch($searchQuery);
                    if ($products && $products->getSize() > 0) {
                        return $products;
                    }
                } catch (Exception $e) {
                    Mage::log('Meilisearch product search failed: ' . $e->getMessage(), null, 'urlmanager_debug.log');
                }
            }

            // Fallback to native Maho search
            $products = $this->searchWithNativeSearch($searchQuery);

            // If no products found, show fallback products
            if ($products->getSize() === 0) {
                return $this->getFallbackProducts();
            }

            return $products;
        } catch (Exception $e) {
            Mage::log('Error in getSuggestedProducts(): ' . $e->getMessage(), null, 'urlmanager_debug.log');
            Mage::logException($e);
            return null;
        }
    }

    /**
     * Extract search term from URL
     */
    protected function extractSearchTermFromUrl(string $url): string
    {
        // URL decode first to handle %20, etc.
        $url = urldecode($url);

        // Remove leading/trailing slashes
        $url = trim($url, '/');

        // Remove .html extension
        $url = str_replace('.html', '', $url);

        // Split by slashes and hyphens, get last meaningful part
        $parts = explode('/', $url);
        $lastPart = end($parts);

        // Convert hyphens and underscores to spaces for search
        $searchTerm = str_replace(['-', '_'], ' ', $lastPart);

        return trim($searchTerm);
    }

    /**
     * Search products using Meilisearch
     */
    protected function searchWithMeilisearch(string $query): ?Mage_Catalog_Model_Resource_Product_Collection
    {
        try {
            // Check if Meilisearch module is available
            if (!class_exists('MeiliSearch\Client')) {
                return null;
            }

            // Get Meilisearch configuration
            $serverUrl = 'http://localhost:7700';
            $apiKey = Mage::helper('core')->decrypt(Mage::getStoreConfig('meilisearch/credentials/api_key'));
            $indexPrefix = 'maho_';

            if (empty($apiKey)) {
                return null;
            }

            $client = new MeiliSearch\Client($serverUrl, $apiKey);
            $indexName = $indexPrefix . Mage::app()->getStore()->getCode() . '_products';

            // Meilisearch handles plurals, stemming, and typos natively - just pass the query as-is
            $results = $client->index($indexName)->search($query, [
                'limit' => $this->getMaxSuggestions(),
                'attributesToRetrieve' => ['objectID'],
            ]);

            $hits = $results->getHits();
            if (empty($hits)) {
                return null;
            }

            // Get product IDs from Meilisearch results
            $productIds = array_map(fn($hit) => $hit['objectID'], $hits);

            // Load products
            $collection = Mage::getResourceModel('catalog/product_collection')
                ->addAttributeToSelect('*')
                ->addFieldToFilter('entity_id', ['in' => $productIds])
                ->addAttributeToFilter('status', 1)
                ->addAttributeToFilter('visibility', ['neq' => 1])
                ->setPageSize($this->getMaxSuggestions());

            $collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInSearchIds());
            Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);

            return $collection;
        } catch (Exception $e) {
            Mage::logException($e);
            return null;
        }
    }

    /**
     * Search products using native Maho search
     */
    protected function searchWithNativeSearch(string $query): Mage_Catalog_Model_Resource_Product_Collection
    {
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('status', 1)
            ->addAttributeToFilter('visibility', ['neq' => 1])
            ->setPageSize($this->getMaxSuggestions());

        // For multi-word searches, add each word as a separate filter (AND logic)
        $words = explode(' ', $query);
        foreach ($words as $word) {
            $word = trim($word);
            if (empty($word)) {
                continue;
            }

            // Strip plural 's' for better matching (e.g., "paddles" matches "paddle")
            $singularWord = $word;
            if (strlen($word) > 4 && str_ends_with($word, 's')) {
                $singularWord = substr($word, 0, -1);
            }

            // Each word must appear in at least ONE of: name, description, or short_description
            // Search for both plural and singular forms
            $collection->addAttributeToFilter(
                [
                    ['attribute' => 'name', 'like' => '%' . $word . '%'],
                    ['attribute' => 'name', 'like' => '%' . $singularWord . '%'],
                    ['attribute' => 'description', 'like' => '%' . $word . '%'],
                    ['attribute' => 'description', 'like' => '%' . $singularWord . '%'],
                    ['attribute' => 'short_description', 'like' => '%' . $word . '%'],
                    ['attribute' => 'short_description', 'like' => '%' . $singularWord . '%'],
                ],
            );
        }

        $collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInSearchIds());
        Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);

        return $collection;
    }

    /**
     * Get fallback products (bestsellers or new arrivals)
     */
    protected function getFallbackProducts(): Mage_Catalog_Model_Resource_Product_Collection
    {
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('status', 1)
            ->addAttributeToFilter('visibility', ['neq' => 1])
            ->setPageSize($this->getMaxSuggestions())
            ->setOrder('created_at', 'DESC');

        $collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInSearchIds());
        Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);

        return $collection;
    }

    /**
     * Get maximum number of suggestions to show
     */
    public function getMaxSuggestions(): int
    {
        return (int) Mage::getStoreConfig('mageaustralia_urlmanager/suggestions/max_suggestions') ?: 5;
    }

    /**
     * Get search term that was extracted
     */
    public function getSearchTerm(): string
    {
        $requestUri = Mage::app()->getRequest()->getRequestUri();
        return $this->extractSearchTermFromUrl($requestUri);
    }

    /**
     * Get suggested categories based on 404 URL
     *
     * @return array|null Array of category objects with name and url
     */
    public function getSuggestedCategories(): ?array
    {
        try {
            Mage::log('getSuggestedCategories() called', null, 'urlmanager_debug.log');

            $searchTerm = $this->getSearchTerm();
            Mage::log('Category search term: ' . $searchTerm, null, 'urlmanager_debug.log');

            if (empty($searchTerm)) {
                Mage::log('Empty search term, returning null', null, 'urlmanager_debug.log');
                return null;
            }

            /** @var MageAustralia_UrlManager_Helper_Data $helper */
            $helper = Mage::helper('mageaustralia_urlmanager');

            // Try Meilisearch first if enabled
            if ($helper->useMeilisearch()) {
                Mage::log('Meilisearch enabled for categories', null, 'urlmanager_debug.log');
                try {
                    if (!class_exists('MeiliSearch\Client')) {
                        Mage::log('MeiliSearch\Client class not found', null, 'urlmanager_debug.log');
                        return null;
                    }

                    $serverUrl = 'http://localhost:7700';
                    $apiKey = Mage::helper('core')->decrypt(Mage::getStoreConfig('meilisearch/credentials/api_key'));
                    $indexPrefix = 'maho_';

                    if (empty($apiKey)) {
                        Mage::log('Meilisearch config missing', null, 'urlmanager_debug.log');
                        return null;
                    }

                    $client = new MeiliSearch\Client($serverUrl, $apiKey);
                    $indexName = $indexPrefix . Mage::app()->getStore()->getCode() . '_categories';
                    Mage::log('Searching index: ' . $indexName, null, 'urlmanager_debug.log');

                    $results = $client->index($indexName)->search($searchTerm, [
                        'limit' => 5,
                        'attributesToRetrieve' => ['objectID', 'name', 'url'],
                    ]);

                    $hits = $results->getHits();
                    Mage::log('Category search results: ' . count($hits) . ' hits', null, 'urlmanager_debug.log');

                    if (!empty($hits)) {
                        $categories = array_map(fn($hit) => (object) [
                            'name' => $hit['name'],
                            'url' => $hit['url'],
                        ], $hits);
                        Mage::log('Returning ' . count($categories) . ' categories', null, 'urlmanager_debug.log');
                        return $categories;
                    } else {
                        Mage::log('No category hits found', null, 'urlmanager_debug.log');
                    }
                } catch (Exception $e) {
                    Mage::log('Category search exception: ' . $e->getMessage(), null, 'urlmanager_debug.log');
                    Mage::logException($e);
                }
            } else {
                Mage::log('Meilisearch NOT enabled for categories', null, 'urlmanager_debug.log');
            }

            return null;
        } catch (Exception $e) {
            Mage::log('Error in getSuggestedCategories(): ' . $e->getMessage(), null, 'urlmanager_debug.log');
            Mage::logException($e);
            return null;
        }
    }

    /**
     * Get suggested CMS pages based on 404 URL
     *
     * @return array|null Array of page objects with title and url
     */
    public function getSuggestedPages(): ?array
    {
        try {
            Mage::log('getSuggestedPages() called', null, 'urlmanager_debug.log');

            $searchTerm = $this->getSearchTerm();
            Mage::log('Page search term: ' . $searchTerm, null, 'urlmanager_debug.log');

            if (empty($searchTerm)) {
                Mage::log('Empty search term, returning null', null, 'urlmanager_debug.log');
                return null;
            }

            /** @var MageAustralia_UrlManager_Helper_Data $helper */
            $helper = Mage::helper('mageaustralia_urlmanager');

            // Try Meilisearch first if enabled
            if ($helper->useMeilisearch()) {
                Mage::log('Meilisearch enabled for pages', null, 'urlmanager_debug.log');
                try {
                    if (!class_exists('MeiliSearch\Client')) {
                        Mage::log('MeiliSearch\Client class not found', null, 'urlmanager_debug.log');
                        return null;
                    }

                    $serverUrl = 'http://localhost:7700';
                    $apiKey = Mage::helper('core')->decrypt(Mage::getStoreConfig('meilisearch/credentials/api_key'));
                    $indexPrefix = 'maho_';

                    if (empty($apiKey)) {
                        Mage::log('Meilisearch config missing', null, 'urlmanager_debug.log');
                        return null;
                    }

                    $client = new MeiliSearch\Client($serverUrl, $apiKey);
                    $indexName = $indexPrefix . Mage::app()->getStore()->getCode() . '_pages';
                    Mage::log('Searching index: ' . $indexName, null, 'urlmanager_debug.log');

                    $results = $client->index($indexName)->search($searchTerm, [
                        'limit' => 5,
                        'attributesToRetrieve' => ['objectID', 'name', 'url'],
                    ]);

                    $hits = $results->getHits();
                    Mage::log('Page search results: ' . count($hits) . ' hits', null, 'urlmanager_debug.log');

                    if (!empty($hits)) {
                        $pages = array_map(fn($hit) => (object) [
                            'title' => $hit['name'],
                            'url' => $hit['url'],
                        ], $hits);
                        Mage::log('Returning ' . count($pages) . ' pages', null, 'urlmanager_debug.log');
                        return $pages;
                    } else {
                        Mage::log('No page hits found', null, 'urlmanager_debug.log');
                    }
                } catch (Exception $e) {
                    Mage::log('Page search exception: ' . $e->getMessage(), null, 'urlmanager_debug.log');
                    Mage::logException($e);
                }
            } else {
                Mage::log('Meilisearch NOT enabled for pages', null, 'urlmanager_debug.log');
            }

            return null;
        } catch (Exception $e) {
            Mage::log('Error in getSuggestedPages(): ' . $e->getMessage(), null, 'urlmanager_debug.log');
            Mage::logException($e);
            return null;
        }
    }
}
