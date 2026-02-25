# Bulk Taxonomy Update (Plus) — Drupal 10/11

UI + Batch + Drush to bulk update taxonomy term reference fields on nodes, with **filters** for:
- Moderation state
- Author
- Workbench Section (choose the section field and term)

## Install
1. Copy `bulk_taxonomy_update_plus` into `web/modules/custom/` (or `modules/custom/`).
2. Enable: `drush en bulk_taxonomy_update -y` (module machine name remains `bulk_taxonomy_update`).

## UI
- Path: `/admin/content/bulk-taxonomy-update`
- Pick **Content type**, **Field to update**, **Term(s)**, **Mode** (set/append/remove).
- Filters:
  - **Moderation state** (auto-detected from assigned workflow)
  - **Author** (user autocomplete)
  - **Section field** + **Section term** (for Workbench Access style sections)
- Optional **Dry run** logs without saving.

## Drush
```
# Replace values
drush btua --type=article --field=field_topic --tids=45 --mode=set

# Append values for a specific author in Published state
drush btua --type=article --field=field_topic --tids=12,34 --mode=append --moderation-state=published --author=5

# Limit to section field + term
drush btua --type=article --field=field_topic --tids=99 --mode=append --section-field=field_section --section-tid=123
```

## Notes
- Field dropdowns only list taxonomy term reference fields on the selected bundle.
- Batch: 50 per UI run, 100 per Drush run.
- Requires Content Moderation if you want the moderation filter.
