# Local Development Setup

## Install prerequisites
- PHP, Composer
- Node, npm/yarn
- MySQL/Postgres
- Drush & CLI tools if applicable

## Bootstrap project
```bash
composer install
cp example.settings.php settings.local.php
# Import DB or install site
```

## Useful commands
- `drush cr`
- `drush cim -y`
- `drush updatedb -y`
