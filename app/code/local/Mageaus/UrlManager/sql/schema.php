<?php

/**
 * Maho
 *
 * @package    Mageaus_UrlManager
 * @copyright  Copyright (c) 2026 Mage Australia
 * @license    https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Declarative equivalent of the legacy sql/mageaus_urlmanager_setup scripts.
 * Legacy scripts stay in place for BC on older cores; `./maho migrate`
 * reconciles from this file idempotently on newer cores.
 *
 * Unique constraints (if ever added) must use addUniqueIndex - see the
 * DDL-drift note in maho-module-generator's DESIGN.md.
 */

declare(strict_types=1);

use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;

return function (Schema $schema): void {
    // Redirect rules. source_url may be a plain path or a full URL
    // (the matcher tries both plus the path component of full URLs);
    // is_wildcard sources may carry the configured wildcard character.
    $redirect = $schema->createTable('mageaus_urlmanager_redirect');
    $redirect->addColumn('redirect_id', Types::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
    $redirect->addColumn('source_url', Types::STRING, ['length' => 255, 'notnull' => true]);
    $redirect->addColumn('destination_url', Types::STRING, ['length' => 255, 'notnull' => true]);
    $redirect->addColumn('status_code', Types::INTEGER, ['notnull' => true, 'default' => 301]);
    $redirect->addColumn('priority', Types::INTEGER, ['notnull' => true, 'default' => 0]);
    $redirect->addColumn('is_wildcard', Types::SMALLINT, ['notnull' => true, 'default' => 0]);
    $redirect->addColumn('is_active', Types::SMALLINT, ['notnull' => true, 'default' => 1]);
    $redirect->addColumn('hit_count', Types::INTEGER, ['notnull' => true, 'default' => 0]);
    $redirect->addColumn('last_hit_at', Types::DATETIME_MUTABLE, ['notnull' => false]);
    $redirect->addColumn('url_key', Types::STRING, ['length' => 255, 'notnull' => false]);
    $redirect->addColumn('status', Types::SMALLINT, ['notnull' => true, 'default' => 1]);
    $redirect->addColumn('created_at', Types::DATETIME_MUTABLE, ['notnull' => true, 'default' => 'CURRENT_TIMESTAMP']);
    $redirect->addColumn('updated_at', Types::DATETIME_MUTABLE, ['notnull' => true, 'default' => 'CURRENT_TIMESTAMP', 'columnDefinition' => 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP']);
    $redirect->addPrimaryKeyConstraint(
        PrimaryKeyConstraint::editor()->setUnquotedColumnNames('redirect_id')->create(),
    );
    $redirect->setComment('URL Manager - redirect rules');

    // 404 log with fuzzy-match suggestion + email-report bookkeeping.
    $log = $schema->createTable('mageaus_urlmanager_notfoundlog');
    $log->addColumn('notfound_log_id', Types::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
    $log->addColumn('request_url', Types::STRING, ['length' => 255, 'notnull' => true, 'comment' => 'URL that triggered 404']);
    $log->addColumn('referer_url', Types::STRING, ['length' => 255, 'notnull' => false, 'comment' => 'Page the user came from']);
    $log->addColumn('user_agent', Types::TEXT, ['notnull' => false, 'comment' => 'Browser user agent string']);
    $log->addColumn('ip_address', Types::STRING, ['length' => 45, 'notnull' => false, 'comment' => 'Client IP address']);
    $log->addColumn('store_id', Types::SMALLINT, ['notnull' => true, 'comment' => 'Store where 404 occurred']);
    $log->addColumn('hit_count', Types::INTEGER, ['notnull' => true, 'default' => 1, 'comment' => 'Times this URL has 404d']);
    $log->addColumn('suggested_product_id', Types::INTEGER, ['notnull' => false, 'comment' => 'Product ID suggested by fuzzy match']);
    $log->addColumn('last_hit_at', Types::DATETIME_MUTABLE, ['notnull' => false]);
    $log->addColumn('status', Types::SMALLINT, ['notnull' => true, 'default' => 1]);
    $log->addColumn('created_at', Types::DATETIME_MUTABLE, ['notnull' => true, 'default' => 'CURRENT_TIMESTAMP']);
    $log->addColumn('updated_at', Types::DATETIME_MUTABLE, ['notnull' => true, 'default' => 'CURRENT_TIMESTAMP', 'columnDefinition' => 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP']);
    $log->addColumn('last_reported_at', Types::DATETIME_MUTABLE, ['notnull' => false, 'comment' => 'Last included in an emailed report; NULL = never reported']);
    $log->addPrimaryKeyConstraint(
        PrimaryKeyConstraint::editor()->setUnquotedColumnNames('notfound_log_id')->create(),
    );
    $log->addIndex(['last_reported_at'], 'IDX_MAGEAUS_URLMANAGER_NOTFOUNDLOG_LAST_REPORTED_AT');
    $log->setComment('URL Manager - 404 log');
};
