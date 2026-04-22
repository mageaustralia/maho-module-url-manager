# Mageaus_UrlManager

URL redirect management + 404 logging + scheduled email reports for Maho.

## Features

- **Redirects** with wildcard support, priority ordering, hit-count tracking, case sensitivity, bulk CSV import/export
- **404 logging** with fuzzy product suggestion (Meilisearch-aware) and auto-cleanup of stale entries
- **Email reports** of top 404 URLs — daily or weekly, configurable recipient + minimum-hits threshold
- **Auto redirects** for disabled / not-visible products and disabled categories
- **Admin UI** for managing redirects and reviewing 404 logs

## Requirements

- Maho 8.3+
- PHP 8.3+

## Installation

```bash
composer require mageaustralia/maho-module-url-manager
composer dump-autoload -o
```

## Changelog

### 1.1.0 (2026-04-23)

- **Fix:** 404 reports now include only URLs hit since the previous report, instead of re-listing the lifetime top 404s every run. Adds `last_reported_at` column + index to `mageaus_urlmanager_notfoundlog`.
- **Fix:** Skip sending the email entirely when there are no new 404s — prevents "empty" reports and acts as idempotent defence-in-depth when the cron scheduler fires twice.
- Stamp `last_reported_at` only after the email sends successfully, so a transient send failure keeps rows eligible for the next run.

### 1.0.0

- Initial release.

## License

OSL-3.0
