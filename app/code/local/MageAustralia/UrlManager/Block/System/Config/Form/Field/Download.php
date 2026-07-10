<?php

/**
 * Maho
 *
 * @category   Mageaus
 * @package    MageAustralia_UrlManager
 * @copyright  Copyright (c) 2025 Maho (https://mahocommerce.com)
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 */

declare(strict_types=1);

/**
 * Download Link Field
 *
 * Displays download link for CSV export in System Configuration
 *
 * @category   Mageaus
 * @package    MageAustralia_UrlManager
 */
class MageAustralia_UrlManager_Block_System_Config_Form_Field_Download extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Render download link HTML
     */
    #[\Override]
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element): string
    {
        $url = Mage::helper('adminhtml')->getUrl('adminhtml/redirect/exportCsv');

        return sprintf(
            '<a href="%s" target="_blank" style="font-weight: bold;">Download</a>',
            htmlspecialchars((string) $url),
        );
    }
}
