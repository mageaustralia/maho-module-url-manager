<?php

/**
 * Maho
 *
 * @package    MageAustralia_UrlManager
 * @copyright  Copyright (c) 2026 Maho (https://mahocommerce.com)
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 */

/**
 * Adds `last_reported_at` so the email-report cron can send only 404s that have
 * been hit since the previous report — instead of re-listing lifetime top-404s
 * every run. Also prevents duplicate emails when the cron scheduler fires twice:
 * the second run sees all relevant rows already stamped and sends nothing.
 */

declare(strict_types=1);

/** @var Mage_Core_Model_Resource_Setup $this */
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn(
    $installer->getTable('mageaustralia_urlmanager/notfoundlog'),
    'last_reported_at',
    [
        'type'    => Maho\Db\Ddl\Table::TYPE_TIMESTAMP,
        'default' => null,
        'comment' => 'Timestamp this 404 was last included in an emailed report; NULL = never reported',
    ],
);

$installer->getConnection()->addIndex(
    $installer->getTable('mageaustralia_urlmanager/notfoundlog'),
    $installer->getIdxName('mageaustralia_urlmanager/notfoundlog', ['last_reported_at']),
    ['last_reported_at'],
);

$installer->endSetup();
