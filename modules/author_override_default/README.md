
# Author Override Default (v2)

Configurable module to default a user reference field (e.g., `field_author_override`) to the **logged-in user** when empty; optionally prefill on forms.

## Features
- Set the **field machine name** in config (default `field_author_override`).
- Scope to **specific content types** or apply to all.
- Clean **service-based** logic used by hooks.
- Drupal 10/11 compatible.

## Install
1. Copy to `/modules/custom/author_override_default`.
2. Enable: `drush en author_override_default -y`
3. Configure: `/admin/config/content/author-override-default`

## Uninstall
`drush pmu author_override_default -y`
