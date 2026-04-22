<?php

/**
 * Maho
 *
 * @category   Mageaus
 * @package    Mageaus_UrlManager
 * @copyright  Copyright (c) 2025 Maho (https://mahocommerce.com)
 */

/**
 * Redirect Model
 *
 * @category   Mageaus
 * @package    Mageaus_UrlManager
 */
class Mageaus_UrlManager_Model_Redirect extends Mage_Core_Model_Abstract
{
    public const STATUS_ENABLED  = 1;
    public const STATUS_DISABLED = 0;

    protected $_eventPrefix = 'mageaus_urlmanager_redirect';
    protected $_eventObject = 'redirect';

    protected function _construct(): void
    {
        $this->_init('mageaus_urlmanager/redirect');
    }

    /**
     * Get available statuses
     */
    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_ENABLED  => Mage::helper('mageaus_urlmanager')->__('Enabled'),
            self::STATUS_DISABLED => Mage::helper('mageaus_urlmanager')->__('Disabled'),
        ];
    }
}
