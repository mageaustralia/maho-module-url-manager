<?php

/**
 * Maho
 *
 * @category   Mageaus
 * @package    Mageaus_UrlManager
 * @copyright  Copyright (c) 2025 Maho (https://mahocommerce.com)
 * @license    https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

declare(strict_types=1);

/**
 * Notfound_log Collection
 *
 * @category   Mageaus
 * @package    Mageaus_UrlManager
 */
class Mageaus_UrlManager_Model_Resource_Notfoundlog_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct(): void
    {
        $this->_init('mageaus_urlmanager/notfoundlog');
    }

    /**
     * Convert collection to option array for dropdowns
     */
    #[\Override]
    public function toOptionArray(): array
    {
        return $this->_toOptionArray('notfound_log_id', 'name');
    }

    /**
     * Convert collection to option hash for filters
     */
    #[\Override]
    public function toOptionHash(): array
    {
        return $this->_toOptionHash('notfound_log_id', 'name');
    }
}
