<?php

/**
 * Maho
 *
 * @copyright  Copyright (c) 2025 Maho (https://mahocommerce.com)
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 */

declare(strict_types=1);

$installer = $this;
$installer->startSetup();


$table = $installer->getConnection()
    ->newTable($installer->getTable('mageaus_urlmanager/redirect'))
    ->addColumn('redirect_id', Maho\Db\Ddl\Table::TYPE_INTEGER, null, [
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        'comment'   => 'ID',
    ], 'ID')
    ->addColumn('source_url', Maho\Db\Ddl\Table::TYPE_TEXT, 255, [
        'nullable'  => false,
        'comment'   => 'Original URL to redirect from',
    ], 'Original URL to redirect from')
    ->addColumn('destination_url', Maho\Db\Ddl\Table::TYPE_TEXT, 255, [
        'nullable'  => false,
        'comment'   => 'Target URL to redirect to',
    ], 'Target URL to redirect to')
    ->addColumn('status_code', Maho\Db\Ddl\Table::TYPE_INTEGER, null, [
        'nullable'  => false,
        'default'   => '301',
        'comment'   => 'HTTP status code (301, 302, etc.)',
    ], 'HTTP status code (301, 302, etc.)')
    ->addColumn('priority', Maho\Db\Ddl\Table::TYPE_INTEGER, null, [
        'nullable'  => false,
        'default'   => '0',
        'comment'   => 'Higher priority redirects are checked first',
    ], 'Higher priority redirects are checked first')
    ->addColumn('is_wildcard', Maho\Db\Ddl\Table::TYPE_SMALLINT, null, [
        'nullable'  => false,
        'default'   => '0',
        'comment'   => 'Whether this redirect uses wildcard matching',
    ], 'Whether this redirect uses wildcard matching')
    ->addColumn('is_active', Maho\Db\Ddl\Table::TYPE_SMALLINT, null, [
        'nullable'  => false,
        'default'   => '1',
        'comment'   => 'Enable/disable this redirect',
    ], 'Enable/disable this redirect')
    ->addColumn('hit_count', Maho\Db\Ddl\Table::TYPE_INTEGER, null, [
        'nullable'  => false,
        'default'   => '0',
        'comment'   => 'Number of times this redirect has been used',
    ], 'Number of times this redirect has been used')
    ->addColumn('last_hit_at', Maho\Db\Ddl\Table::TYPE_DATETIME, null, [
        'comment'   => 'Last time this redirect was used',
    ], 'Last time this redirect was used')
    ->addColumn('url_key', Maho\Db\Ddl\Table::TYPE_VARCHAR, 255, [
        'nullable'  => true,
        'comment'   => 'URL Key',
    ], 'URL Key')
    ->addColumn('status', Maho\Db\Ddl\Table::TYPE_SMALLINT, null, [
        'nullable'  => false,
        'default'   => '1',
        'comment'   => 'Status',
    ], 'Status')
    ->addColumn('created_at', Maho\Db\Ddl\Table::TYPE_TIMESTAMP, null, [
        'nullable'  => false,
        'default'   => Maho\Db\Ddl\Table::TIMESTAMP_INIT,
        'comment'   => 'Created At',
    ], 'Created At')
    ->addColumn('updated_at', Maho\Db\Ddl\Table::TYPE_TIMESTAMP, null, [
        'nullable'  => false,
        'default'   => Maho\Db\Ddl\Table::TIMESTAMP_INIT_UPDATE,
        'comment'   => 'Updated At',
    ], 'Updated At')
    ->setComment('redirect Table');

$installer->getConnection()->createTable($table);


$table = $installer->getConnection()
    ->newTable($installer->getTable('mageaus_urlmanager/notfound_log'))
    ->addColumn('notfound_log_id', Maho\Db\Ddl\Table::TYPE_INTEGER, null, [
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        'comment'   => 'ID',
    ], 'ID')
    ->addColumn('request_url', Maho\Db\Ddl\Table::TYPE_TEXT, 255, [
        'nullable'  => false,
        'comment'   => 'URL that triggered 404',
    ], 'URL that triggered 404')
    ->addColumn('referer_url', Maho\Db\Ddl\Table::TYPE_TEXT, 255, [
        'nullable'  => true,
        'comment'   => 'Page the user came from',
    ], 'Page the user came from')
    ->addColumn('user_agent', Maho\Db\Ddl\Table::TYPE_TEXT, '64k', [
        'nullable'  => true,
        'comment'   => 'Browser user agent string',
    ], 'Browser user agent string')
    ->addColumn('ip_address', Maho\Db\Ddl\Table::TYPE_TEXT, 45, [
        'nullable'  => true,
        'comment'   => 'Client IP address',
    ], 'Client IP address')
    ->addColumn('store_id', Maho\Db\Ddl\Table::TYPE_SMALLINT, null, [
        'nullable'  => false,
        'comment'   => 'Store where 404 occurred',
    ], 'Store where 404 occurred')
    ->addColumn('hit_count', Maho\Db\Ddl\Table::TYPE_INTEGER, null, [
        'nullable'  => false,
        'default'   => '1',
        'comment'   => 'Number of times this URL has 404ed',
    ], 'Number of times this URL has 404ed')
    ->addColumn('suggested_product_id', Maho\Db\Ddl\Table::TYPE_INTEGER, null, [
        'nullable'  => true,
        'comment'   => 'Product ID suggested by fuzzy match',
    ], 'Product ID suggested by fuzzy match')
    ->addColumn('last_hit_at', Maho\Db\Ddl\Table::TYPE_DATETIME, null, [
        'comment'   => 'Last time this 404 was triggered',
    ], 'Last time this 404 was triggered')
    ->addColumn('status', Maho\Db\Ddl\Table::TYPE_SMALLINT, null, [
        'nullable'  => false,
        'default'   => '1',
        'comment'   => 'Status',
    ], 'Status')
    ->addColumn('created_at', Maho\Db\Ddl\Table::TYPE_TIMESTAMP, null, [
        'nullable'  => false,
        'default'   => Maho\Db\Ddl\Table::TIMESTAMP_INIT,
        'comment'   => 'Created At',
    ], 'Created At')
    ->addColumn('updated_at', Maho\Db\Ddl\Table::TYPE_TIMESTAMP, null, [
        'nullable'  => false,
        'default'   => Maho\Db\Ddl\Table::TIMESTAMP_INIT_UPDATE,
        'comment'   => 'Updated At',
    ], 'Updated At')
    ->setComment('notfound_log Table');

$installer->getConnection()->createTable($table);


$installer->endSetup();
