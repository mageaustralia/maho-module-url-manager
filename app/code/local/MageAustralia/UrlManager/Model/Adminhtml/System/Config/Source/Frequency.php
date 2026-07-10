<?php

/**
 * Maho
 *
 * @category   MageAustralia
 * @package    MageAustralia_UrlManager
 * @copyright  Copyright (c) 2025 Maho (https://mahocommerce.com)
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 */

declare(strict_types=1);

/**
 * Email Report Frequency Source Model
 *
 * @category   MageAustralia
 * @package    MageAustralia_UrlManager
 */
class MageAustralia_UrlManager_Model_Adminhtml_System_Config_Source_Frequency
{
    public const FREQUENCY_DAILY = 'daily';
    public const FREQUENCY_WEEKLY = 'weekly';

    /**
     * Get frequency options
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::FREQUENCY_DAILY,
                'label' => Mage::helper('mageaustralia_urlmanager')->__('Daily'),
            ],
            [
                'value' => self::FREQUENCY_WEEKLY,
                'label' => Mage::helper('mageaustralia_urlmanager')->__('Weekly'),
            ],
        ];
    }
}
