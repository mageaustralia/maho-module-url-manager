<?php

/**
 * Maho
 *
 * @category   Mageaus
 * @package    Mageaus_UrlManager
 * @copyright  Copyright (c) 2025 Maho (https://mahocommerce.com)
 */

/**
 * URL rewrite observer for Redirect
 *
 * @category   Mageaus
 * @package    Mageaus_UrlManager
 */
class Mageaus_UrlManager_Model_Observer_UrlRewrite
{
    /**
     * Generate URL rewrites after save
     */
    public function generateRedirectUrlRewrite(Varien_Event_Observer $observer): void
    {
        /** @var Mageaus_UrlManager_Model_Redirect $redirect */
        $redirect = $observer->getEvent()->getRedirect();

        if (!$redirect->getId()) {
            return;
        }

        // Generate URL rewrite (url_key should already be set)
        if ($redirect->getUrlKey()) {
            $this->_createUrlRewrite($redirect);
        }
    }

    /**
     * Generate URL key from name/title
     */
    protected function _generateUrlKey(Mageaus_UrlManager_Model_Redirect $redirect): string
    {
        $name = $redirect->getName() ?? $redirect->getTitle() ?? '';
        $urlKey = Mage::helper('catalog/product_url')->format($name);

        // Ensure uniqueness
        $suffix = '';
        $i = 0;
        $collection = Mage::getResourceModel('mageaus_urlmanager/redirect_collection');

        do {
            $collection->clear();
            $collection->addFieldToFilter('url_key', $urlKey . $suffix);
            if ($redirect->getId()) {
                $collection->addFieldToFilter('redirect_id', ['neq' => $redirect->getId()]);
            }

            if ($collection->getSize() > 0) {
                $i++;
                $suffix = '-' . $i;
            } else {
                break;
            }
        } while (true);

        return $urlKey . $suffix;
    }

    /**
     * Create URL rewrite
     */
    protected function _createUrlRewrite(Mageaus_UrlManager_Model_Redirect $redirect): void
    {
        $stores = $redirect->getStoreId() ? [$redirect->getStoreId()] : Mage::app()->getStores();

        foreach ($stores as $store) {
            if ($store instanceof Mage_Core_Model_Store) {
                $storeId = $store->getId();
            } else {
                $storeId = $store;
            }

            // Delete old rewrite
            Mage::getModel('core/url_rewrite')
                ->getCollection()
                ->addFieldToFilter('id_path', 'mageaus_urlmanager/redirect/' . $redirect->getId())
                ->addFieldToFilter('store_id', $storeId)
                ->walk('delete');

            // Create new rewrite
            $urlRewrite = Mage::getModel('core/url_rewrite');
            $urlRewrite->setData([
                'store_id' => $storeId,
                'id_path' => 'mageaus_urlmanager/redirect/' . $redirect->getId(),
                'request_path' => 'mageaus_urlmanager/redirect/' . $redirect->getUrlKey(),
                'target_path' => 'mageaus_urlmanager/redirect/view/id/' . $redirect->getId(),
                'is_system' => 1,
            ]);

            try {
                $urlRewrite->save();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }

    /**
     * Delete URL rewrites after delete
     */
    public function deleteRedirectUrlRewrite(Varien_Event_Observer $observer): void
    {
        /** @var Mageaus_UrlManager_Model_Redirect $redirect */
        $redirect = $observer->getEvent()->getRedirect();

        if (!$redirect->getId()) {
            return;
        }

        // Delete all rewrites for this item
        Mage::getModel('core/url_rewrite')
            ->getCollection()
            ->addFieldToFilter('id_path', 'mageaus_urlmanager/redirect/' . $redirect->getId())
            ->walk('delete');
    }
}
