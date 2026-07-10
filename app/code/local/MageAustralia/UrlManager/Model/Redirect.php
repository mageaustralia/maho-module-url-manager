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
 * Redirect Model
 *
 * @category   Mageaus
 * @package    MageAustralia_UrlManager
 */
class MageAustralia_UrlManager_Model_Redirect extends Mage_Core_Model_Abstract
{
    public const STATUS_ENABLED  = 1;
    public const STATUS_DISABLED = 0;

    protected $_eventPrefix = 'mageaustralia_urlmanager_redirect';
    protected $_eventObject = 'redirect';

    protected function _construct(): void
    {
        $this->_init('mageaustralia_urlmanager/redirect');
    }

    /**
     * Get available statuses
     */
    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_ENABLED  => Mage::helper('mageaustralia_urlmanager')->__('Enabled'),
            self::STATUS_DISABLED => Mage::helper('mageaustralia_urlmanager')->__('Disabled'),
        ];
    }
}
