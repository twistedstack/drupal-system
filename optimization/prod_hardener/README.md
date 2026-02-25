# Prod Hardener

A lightweight Drupal 10/11 module that helps keep production clean and fast:

- Disables configured "dev" modules when the site is detected as production
- Forces optimal performance settings (CSS/JS aggregation, Twig cache)
- Logs actions to the `prod_hardener` channel
- Provides a settings form at `/admin/config/development/prod-hardener`

## How production is detected

- If `$_ENV['DRUPAL_ENV'] === 'prod'`, or
- If `$settings['prod_hardener_env']` is set to `'prod'` in `settings.php`

## Quick start

1. Copy the `prod_hardener` folder to `/modules/custom/prod_hardener`.
2. Enable the module.
3. Visit `/admin/config/development/prod-hardener` and list dev modules to auto-disable.
4. Set `DRUPAL_ENV=prod` (or `$settings['prod_hardener_env'] = 'prod';`) on production.