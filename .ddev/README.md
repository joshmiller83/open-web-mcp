# DDEV Configuration

This project uses [DDEV](https://ddev.com/) for local development.

## Quick Start

```bash
# Install DDEV: https://ddev.readthedocs.io/en/stable/#installation
ddev start
ddev composer install
ddev drush site:install --existing-config -y
ddev drush uli
```

The site will be available at: https://open-web-mcp.ddev.site
