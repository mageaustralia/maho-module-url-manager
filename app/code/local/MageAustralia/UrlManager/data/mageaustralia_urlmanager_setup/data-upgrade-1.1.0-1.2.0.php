<?php

/**
 * Maho
 *
 * @package    MageAustralia_UrlManager
 * @copyright  Copyright (c) 2026 Mage Australia
 * @license    https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Backfill catalog_confidence for rows logged before the column existed.
 *
 * Without this every existing row sits at the 0 default, so the first report
 * after the upgrade would be empty and the first cleanup sweep would treat
 * genuine catalog 404s as probes and delete them first - the exact data loss
 * the column was added to prevent.
 *
 * DML, so it lives in data/ rather than sql/: the declarative schema engine
 * must have materialised the column before this runs.
 */

declare(strict_types=1);

/** @var Mage_Core_Model_Resource_Setup $this */
$this->startSetup();

/** @var MageAustralia_UrlManager_Helper_Data $helper */
$helper = Mage::helper('mageaustralia_urlmanager');

$connection = $this->getConnection();
$table = $this->getTable('mageaustralia_urlmanager/notfoundlog');

$select = $connection->select()->from($table, ['notfound_log_id', 'request_url']);

// Group ids by confidence so the write is three statements, not one per row.
$byConfidence = [];
foreach ($connection->fetchAll($select) as $row) {
    $confidence = $helper->getCatalogConfidence((string) $row['request_url']);
    $byConfidence[$confidence][] = (int) $row['notfound_log_id'];
}

foreach ($byConfidence as $confidence => $ids) {
    foreach (array_chunk($ids, 500) as $chunk) {
        $connection->update(
            $table,
            ['catalog_confidence' => (int) $confidence],
            ['notfound_log_id IN (?)' => $chunk],
        );
    }
}

$this->endSetup();
