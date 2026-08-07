<?php

/**
 * Maho
 *
 * @category   MageAustralia
 * @package    MageAustralia_UrlManager
 * @copyright  Copyright (c) 2026 Maho (https://mahocommerce.com) & MageAustralia (https://mageaustralia.com.au)
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 */

declare(strict_types=1);

/**
 * Renders a URL grid cell that cannot blow out the grid width.
 *
 * Logged 404 URLs can carry very long query strings, and a single unbroken
 * token has no wrap opportunity, so the column stretches and forces the whole
 * grid to scroll horizontally. Wrap on any character and cap the cell width;
 * the untruncated value stays available via the title tooltip.
 */
class MageAustralia_UrlManager_Block_Adminhtml_Notfoundlog_Renderer_Url extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public const MAX_VISIBLE_LENGTH = 120;

    #[\Override]
    public function render(\Maho\DataObject $row)
    {
        $value = (string) $row->getData($this->getColumn()->getIndex());

        if ($value === '') {
            return '';
        }

        $display = $value;
        if (mb_strlen($display) > self::MAX_VISIBLE_LENGTH) {
            $display = mb_substr($display, 0, self::MAX_VISIBLE_LENGTH) . '...';
        }

        return sprintf(
            '<span style="display:inline-block; max-width:32em; word-break:break-all; white-space:normal;" title="%s">%s</span>',
            $this->escapeHtml($value),
            $this->escapeHtml($display),
        );
    }
}
