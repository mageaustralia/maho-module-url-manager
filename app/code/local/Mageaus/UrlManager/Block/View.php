<?php

/**
 * Maho
 *
 * @category   Mageaus
 * @package    Mageaus_UrlManager
 * @copyright  Copyright (c) 2025 Maho (https://mahocommerce.com)
 */

/**
 * Redirect View Block
 *
 * @category   Mageaus
 * @package    Mageaus_UrlManager
 */
class Mageaus_UrlManager_Block_View extends Mage_Core_Block_Template
{
    /**
     * Get current item
     *
     * @return Mageaus_UrlManager_Model_Redirect
     */
    public function getRedirect()
    {
        return Mage::registry('current_redirect');
    }

    /**
     * Get back URL
     */
    public function getBackUrl(): string
    {
        return $this->getUrl('*/*/');
    }
}
