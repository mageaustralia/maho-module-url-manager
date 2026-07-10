<?php

/**
 * Maho
 *
 * @category   MageAustralia
 * @package    MageAustralia_UrlManager
 * @copyright  Copyright (c) 2025 Maho (https://mahocommerce.com)
 * @license    https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

declare(strict_types=1);

/**
 * Notfoundlog Model
 *
 * @category   MageAustralia
 * @package    MageAustralia_UrlManager
 */
class MageAustralia_UrlManager_Model_Notfoundlog extends Mage_Core_Model_Abstract
{
    public const STATUS_ENABLED  = 1;
    public const STATUS_DISABLED = 0;

    protected $_eventPrefix = 'mageaustralia_urlmanager_notfound_log';
    protected $_eventObject = 'notfound_log';

    protected function _construct(): void
    {
        $this->_init('mageaustralia_urlmanager/notfoundlog');
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
