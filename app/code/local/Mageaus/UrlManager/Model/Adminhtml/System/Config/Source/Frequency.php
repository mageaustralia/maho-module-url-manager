<?php

/**
 * Maho
 *
 * @category   Mageaus
 * @package    Mageaus_UrlManager
 * @copyright  Copyright (c) 2025 Maho (https://mahocommerce.com)
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 */

declare(strict_types=1);

/**
 * Email Report Frequency Source Model
 *
 * @category   Mageaus
 * @package    Mageaus_UrlManager
 */
class Mageaus_UrlManager_Model_Adminhtml_System_Config_Source_Frequency
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
                'label' => Mage::helper('mageaus_urlmanager')->__('Daily'),
            ],
            [
                'value' => self::FREQUENCY_WEEKLY,
                'label' => Mage::helper('mageaus_urlmanager')->__('Weekly'),
            ],
        ];
    }
}
