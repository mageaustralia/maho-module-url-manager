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
 * Redirect View Block
 *
 * @category   Mageaus
 * @package    MageAustralia_UrlManager
 */
class MageAustralia_UrlManager_Block_View extends Mage_Core_Block_Template
{
    /**
     * Get current item
     *
     * @return MageAustralia_UrlManager_Model_Redirect
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
