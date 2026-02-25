# Auto Archive+

Enhanced auto-archiving for Drupal with per-content type settings, notifications, logs, Drush integration, and a manual test button.

## Features
- Per content type thresholds and optional date field.
- "Do not auto-archive" flag support.
- Email and internal notifications 7 days before archive.
- Log entity with admin view at /admin/content/auto-archive-log.
- Drush command `drush auto-archive:run`.
- Dashboard block with summary of scheduled archives.
- Hooks for pre/post archive customization.
- ✅ Manual "Run Now" button on settings page.

## Installation
1. Place `auto_archive_plus` in `modules/custom/`.
2. Enable with `drush en auto_archive_plus -y`.
3. Configure at `/admin/config/content/auto-archive-plus`.

## Notes
- Requires Content Moderation module.
- Designed for Drupal ^10 || ^11.
