<?php

/**
 * Maho
 *
 * @category   Mageaus
 * @package    MageAustralia_UrlManager
 * @copyright  Copyright (c) 2025 Maho (https://mahocommerce.com)
 * @license    https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

declare(strict_types=1);

/**
 * URL rewrite observer for Redirect
 *
 * @category   Mageaus
 * @package    MageAustralia_UrlManager
 */
class MageAustralia_UrlManager_Model_Observer_UrlRewrite
{
    /**
     * Generate URL rewrites after save
     */
    public function generateRedirectUrlRewrite(\Maho\Event\Observer $observer): void
    {
        /** @var MageAustralia_UrlManager_Model_Redirect $redirect */
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
    protected function _generateUrlKey(MageAustralia_UrlManager_Model_Redirect $redirect): string
    {
        $name = $redirect->getName() ?? $redirect->getTitle() ?? '';
        $urlKey = Mage::helper('catalog/product_url')->format($name);

        // Ensure uniqueness
        $suffix = '';
        $i = 0;
        $collection = Mage::getResourceModel('mageaustralia_urlmanager/redirect_collection');

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
    protected function _createUrlRewrite(MageAustralia_UrlManager_Model_Redirect $redirect): void
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
                ->addFieldToFilter('id_path', 'mageaustralia_urlmanager/redirect/' . $redirect->getId())
                ->addFieldToFilter('store_id', $storeId)
                ->walk('delete');

            // Create new rewrite
            $urlRewrite = Mage::getModel('core/url_rewrite');
            $urlRewrite->setData([
                'store_id' => $storeId,
                'id_path' => 'mageaustralia_urlmanager/redirect/' . $redirect->getId(),
                'request_path' => 'mageaustralia_urlmanager/redirect/' . $redirect->getUrlKey(),
                'target_path' => 'mageaustralia_urlmanager/redirect/view/id/' . $redirect->getId(),
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
    public function deleteRedirectUrlRewrite(\Maho\Event\Observer $observer): void
    {
        /** @var MageAustralia_UrlManager_Model_Redirect $redirect */
        $redirect = $observer->getEvent()->getRedirect();

        if (!$redirect->getId()) {
            return;
        }

        // Delete all rewrites for this item
        Mage::getModel('core/url_rewrite')
            ->getCollection()
            ->addFieldToFilter('id_path', 'mageaustralia_urlmanager/redirect/' . $redirect->getId())
            ->walk('delete');
    }
}
