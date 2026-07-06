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
 * CSV Instructions Field
 *
 * Displays CSV format instructions in System Configuration
 *
 * @category   Mageaus
 * @package    Mageaus_UrlManager
 */
class Mageaus_UrlManager_Block_System_Config_Form_Field_Instructions extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Render instructions HTML
     */
    #[\Override]
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element): string
    {
        $helper = Mage::helper('mageaus_urlmanager');
        $wildcardChar = $helper->getWildcardCharacter() ?: '*';

        return sprintf(
            '<div style="padding: 10px; background: #f8f8f8; border: 1px solid #ddd; margin-bottom: 10px;">
                <p><strong>CSV Format (takes three Excel cells):</strong></p>
                <p><code>http://domain.com/old-path/,http://domain.com/new-path/,301</code></p>

                <p style="margin-top: 15px;"><strong>You can use wildcards!</strong></p>
                <p><code>http://domain.com/old-%s,http://domain.com/new-path/,301</code></p>

                <p style="margin-top: 10px;">The above example will redirect every 404 URL starting with <code>http://domain.com/old-</code> to <code>http://domain.com/new-path/</code></p>

                <p style="margin-top: 15px; font-size: 12px; color: #666;">
                    To cater to different CSV formats, I have provided separator and wildcard customisation.
                    Please check the CSV file by opening it in a plain text editor.
                    Formatting errors and additional carriage returns can cause the module to stop working.
                </p>
            </div>',
            htmlspecialchars((string) $wildcardChar),
        );
    }
}
