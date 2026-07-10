# MageAustralia_UrlManager

[![CI](https://github.com/mageaustralia/maho-module-url-manager/actions/workflows/ci.yml/badge.svg)](https://github.com/mageaustralia/maho-module-url-manager/actions/workflows/ci.yml)
[![License: OSL-3.0](https://img.shields.io/badge/license-OSL--3.0-blue.svg)](LICENSE)

URL redirect management + 404 logging + scheduled email reports for Maho.

## Features

- **Redirects** with wildcard support, priority ordering, hit-count tracking, case sensitivity, bulk CSV import/export
- **404 logging** with fuzzy product suggestion (Meilisearch-aware), configurable ignore patterns, and auto-cleanup of stale entries
- **Email reports** of top 404 URLs - daily or weekly, configurable recipient + minimum-hits threshold
- **Auto redirects** for disabled / not-visible products and disabled categories
- **Admin UI** for managing redirects and reviewing 404 logs

## Requirements

- Maho 26.5+ (tested on 26.7)
- PHP 8.3+

## Installation

The package is installed from GitHub (not on Packagist). Add the repository to your Maho project's `composer.json`, then require it:

```bash
composer config repositories.maho-module-url-manager vcs https://github.com/mageaustralia/maho-module-url-manager
composer require mageaustralia/maho-module-url-manager
```

Then from your Maho root:

```bash
composer dump-autoload        # compiles the module's routes + observers
./maho migrate                # creates/reconciles the two tables (declarative schema)
./maho cache:flush
```

Configuration lives under **System > Configuration > URL Manager** (enable/disable, wildcard character, case sensitivity, query-string handling, 404 email reports). Redirects and the 404 log are managed under the **URL Manager** admin menu.

### Manual install (no composer)

Copy the `app/` tree into your Maho root, then run the same three commands above.

## Migrating from Mageaus_UrlManager (pre-1.3.0)

Version 1.3.0 renamed the vendor namespace from `Mageaus` to `MageAustralia`. Existing installs must migrate DB identifiers before deploying the new code:

```sql
RENAME TABLE mageaus_urlmanager_redirect TO mageaustralia_urlmanager_redirect;
RENAME TABLE mageaus_urlmanager_notfoundlog TO mageaustralia_urlmanager_notfoundlog;
UPDATE core_resource SET code = 'mageaustralia_urlmanager_setup' WHERE code = 'mageaus_urlmanager_setup';
UPDATE core_config_data SET path = REPLACE(path, 'mageaus_urlmanager/', 'mageaustralia_urlmanager/') WHERE path LIKE 'mageaus_urlmanager/%';
```

Also update any CMS pages/blocks or custom layout XML that reference `mageaus_urlmanager/*` block types or `mageaus/urlmanager/*` templates, remove the old `app/code/local/Mageaus/UrlManager` tree and `app/etc/modules/Mageaus_UrlManager.xml`, then run `composer dump-autoload` and `./maho cache:flush`.

## Changelog

### 1.3.0 (2026-07-10)

- **Rename:** vendor namespace `Mageaus` -> `MageAustralia` (class prefix, aliases, config section, table names, setup resource, design paths). See migration notes above.
- **Feature:** 404-logging ignore patterns - exclude URLs from the 404 log by substring match (one pattern per line, validated on save).
- **Fix:** install script referenced undeclared entity `notfound_log`; fresh installs of the 404 log table would fail.
- Replaced remaining `Varien_*` type hints with `Maho\*` equivalents.

### 1.2.0 (2026-07-06)

- **Fix:** host-agnostic redirect matching — sources stored as full URLs (the common shape for imported redirect sets) now also match by their path component, so the same redirect table works on any environment host (dev/staging/production), not just the host the URLs were anchored to.
- **Fix:** homepage `TypeError` under `strict_types` (`strtok('', '?')` returns `false` on an empty path).
- Declarative `sql/schema.php` for both tables (legacy setup scripts retained for BC).
- Modernisation: `declare(strict_types=1)` across the module, `Maho\Data\Form` in admin forms, license headers throughout, 136 lines of dead duplicate matcher code removed, per-row debug logging gated behind developer mode.
- CI: lint workflow from [maho-module-generator](https://github.com/mageaustralia/maho-module-generator) added alongside maho-ci checks.

### 1.1.0 (2026-04-23)

- **Fix:** 404 reports now include only URLs hit since the previous report, instead of re-listing the lifetime top 404s every run. Adds `last_reported_at` column + index to `mageaustralia_urlmanager_notfoundlog`.
- **Fix:** Skip sending the email entirely when there are no new 404s — prevents "empty" reports and acts as idempotent defence-in-depth when the cron scheduler fires twice.
- Stamp `last_reported_at` only after the email sends successfully, so a transient send failure keeps rows eligible for the next run.

### 1.0.0

- Initial release.

## License

OSL-3.0
