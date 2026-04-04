# Auto Archive+ Module


Auto Archive+ automatically archives old content based on configurable rules per content type.

It supports moderation-state archiving, date field selection, cron execution, manual execution, and logging with a built-in admin report.

Compatible with Drupal 10 and Drupal 11.

---

## Features

* Archive content after X days (per content type)
* Optional custom date field per content type
* Automatic cron execution
* Manual “Run Now” button
* Moderation-state archiving (published → archived)
* Auto Archive Log entity
* Admin log page
* Auto-installed View report
* Edit links for archived nodes

---

## Requirements

* Drupal 10 or 11
* Node module
* Views module
* Content Moderation (if using moderation-state archiving)

---

## Installation

Place the module in:

```
modules/custom/auto_archive_plus
```

Enable:

```
drush en auto_archive_plus -y
drush cr
```

---

## Configuration

```
/admin/config/content/auto-archive-plus
```

### For each content type you can:

* Enable or disable archiving
* Set archive threshold (in days)
* Select a date field (optional)

If no date field is selected, the node created date is used.

---

## How It Works

### Default Behavior

Uses the node created timestamp.

* Stored as UNIX timestamp
* Compared numerically
* Fast and reliable

### Custom Date Field

Supports:

* `timestamp` fields
* `datetime` fields
* `date` fields

The module automatically detects field type and compares correctly.

---

## Moderation-State Archiving

If the content type uses Content Moderation:

* Only nodes in `published` state are archived
* Nodes are moved to `archived` moderation state

If moderation is not enabled, nodes are skipped.

---

## Archive Log

Every archived node creates a log entry stored in `auto_archive_log` entity

### Fields logged:

* Node ID
* Title
* Content type
* Archived date

### Admin Log Page

```
/admin/content/auto-archive-log
```

Includes:

* Linked node ID
* Linked title
* Edit button
* Sortable columns

---

## Cron Integration

The module runs automatically via Drupal cron:

```
hook_cron()
```

To test manually:

```
drush cron
```

Or click Run Auto Archive Now in the settings page.

---

## Date Handling Logic

| Field Type | Storage Format | Comparison Type |
| -- | -- | -- |
| created | UNIX timestamp | Numeric |
| timestamp | UNIX timestamp | Numeric |
| datetime | ISO string | String compare |
| date | Y-m-d | String compare |

The module automatically detects the field type before comparing.

---

## Permissions

* Administer Auto Archive+
* Run Auto Archive+ manually
* View Auto Archive Log

### Assign to roles:

* Tier 1 Admin
* Senior Developer
* Developer

---

## Architecture Overview

1. Settings form stores rules in config.
2. Cron or manual trigger runs service.
3. Service queries nodes by bundle + threshold.
4. Nodes moved to archived moderation state.
5. Log entry created.
6. Admin view displays archive history.

---

## Extensibility

* Add pre/post archive hooks
* Add batch processing for very large sites
* Extend logging fields
* Add CSV export to View

---

## Troubleshooting

### Moderation State Error

If you see:

```
'mode ration_state' not found
```

Ensure:

* Content Moderation is enabled
* Workflow is attached to the content type

### View Not Installed

If the log view does not appear:

* Reinstall module (config/install only imports on install)
* Or import config manually

## 

## Metadata
- URL: [https://linear.app/drupalstack/issue/DRU-227/auto-archive-module](https://linear.app/drupalstack/issue/DRU-227/auto-archive-module)
- Identifier: DRU-227
- Status: In Progress
- Priority: No priority
- Assignee: Kelly Rock
- Created: 2026-02-24T02:20:33.380Z
- Updated: 2026-03-05T08:58:04.091Z

## Sub-issues

- [DRU-241 Module Notes](https://linear.app/drupalstack/issue/DRU-241/module-notes)
- [DRU-242 How To Use Auto Archive+ in Drupal Admin](https://linear.app/drupalstack/issue/DRU-242/how-to-use-auto-archive-in-drupal-admin)
