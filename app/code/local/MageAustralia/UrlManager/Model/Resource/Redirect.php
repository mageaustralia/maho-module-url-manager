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
 * Redirect Resource Model
 *
 * @category   Mageaus
 * @package    MageAustralia_UrlManager
 */
class MageAustralia_UrlManager_Model_Resource_Redirect extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct(): void
    {
        $this->_init('mageaustralia_urlmanager/redirect', 'redirect_id');
    }
}
