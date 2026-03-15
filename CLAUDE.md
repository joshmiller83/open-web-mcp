# Claude Project Memory — open-web-mcp

## Project Overview

Drupal CMS site that exposes event management tools via the MCP (Model Context Protocol) module, deployed on Pantheon. The MCP endpoint allows AI assistants to query events, speakers, and handle registrations anonymously.

- **Pantheon site:** `mcp-demo`
- **Dev URL:** `https://dev-mcp-demo.pantheonsite.io`
- **MCP endpoint:** `https://dev-mcp-demo.pantheonsite.io/mcp/post` (POST, anonymous)
- **Local URL:** `https://open-web-mcp.ddev.site`

---

## Local Development (DDEV)

- All drush commands run via `ddev exec drush` (e.g. `ddev exec drush cr`, `ddev exec drush cim -y`)
- The local site is at `https://open-web-mcp.ddev.site`
- DDEV is configured with Pantheon credentials via `DDEV_PANTHEON_SITE` and `DDEV_PANTHEON_ENVIRONMENT` in `.ddev/config.yaml`

### Pushing local DB to Pantheon

Stream the local DB directly without temp files:

```bash
ddev auth ssh
ddev export-db --gzip=false 2>/dev/null | ddev exec terminus drush mcp-demo.dev -- sqlc
```

Then rebuild cache:

```bash
ddev exec terminus drush mcp-demo.dev -- cr
```

### Pulling Pantheon DB locally

```bash
ddev pull pantheon --skip-files -y
```

---

## Pantheon Deployment

### GitHub Actions

- Defined in `.github/workflows/deploy-pantheon.yml`
- Triggers on push to `main` and PRs
- Uses `pantheon-systems/push-to-pantheon@0.9.0`
- Secrets live in the **`open_web_build` GitHub Environment** (not repo-level secrets) — the job must specify `environment: open_web_build`
- Secrets needed: `PANTHEON_SSH_KEY`, `PANTHEON_MACHINE_TOKEN`, `PANTHEON_SITE`

### pantheon.yml — critical settings

```yaml
api_version: 1
build_step: true      # Triggers Integrated Composer (runs composer install on Pantheon)
php_version: 8.3
web_docroot: true     # REQUIRED — tells Pantheon the docroot is web/, not repo root
                      # Without this, nginx serves from /code instead of /code/web
                      # and the site shows "No Site Detected" even though drush works fine
```

### Integrated Composer

- Pantheon runs `composer install` automatically when `build_step: true` is set
- This means `vendor/` and `web/modules/contrib/` do NOT need to be committed
- Source code only goes to GitHub/Pantheon git; Pantheon builds artifacts

### Connection mode

- Pantheon dev must be in **Git mode** to accept code pushes:
  ```bash
  ddev exec terminus connection:set mcp-demo.dev git
  ```

### Terminus via DDEV

Use `ddev exec terminus` for all Pantheon CLI operations:

```bash
ddev exec terminus drush mcp-demo.dev -- cr
ddev exec terminus drush mcp-demo.dev -- cim -y
ddev exec terminus drush mcp-demo.dev -- sqlq "SHOW TABLES"
ddev exec terminus workflow:list mcp-demo
ddev exec terminus env:wake mcp-demo.dev
```

---

## MCP Plugin

### Location

`web/modules/custom/open_web_exchange/src/Plugin/Mcp/OpenWebExchange.php`

### Key implementation details

- **Plugin ID must use hyphens**, not underscores — the MCP module validates IDs against `/^[a-zA-Z0-9-]+$/`
  - Correct: `open-web-exchange`
  - Wrong: `open_web_exchange`
- **Anonymous access** requires overriding `getAllowedRoles()` to return `[]`:
  ```php
  public function getAllowedRoles(): array {
      return [];
  }
  ```
  Setting `roles: {}` in config alone does NOT work — `NestedArray::mergeDeep()` always injects the `['authenticated']` default.
- The plugin provides 5 tools: `query_events`, `get_event_details`, `register_for_event`, `suggest_events`, `get_speaker_info`

### Config sync

`config/sync/mcp.settings.yml` must include the plugin entry:

```yaml
plugins:
  open-web-exchange:
    enabled: true
    roles: {  }
    config: {  }
    tools: {  }
```

`config/sync/user.role.anonymous.yml` must include:

```yaml
dependencies:
  module:
    - mcp
permissions:
  - 'use mcp server'
```

---

## settings.php — Pantheon DB Connection

Pantheon does **not** populate `$_ENV` in PHP — use `getenv()` instead:

```php
if (defined('PANTHEON_ENVIRONMENT')) {
  $databases['default']['default'] = [
    'driver'    => 'mysql',
    'database'  => getenv('DB_NAME'),
    'username'  => getenv('DB_USER'),
    'password'  => getenv('DB_PASSWORD'),
    'host'      => getenv('DB_HOST'),
    'port'      => getenv('DB_PORT'),
    'prefix'    => '',
    'collation' => 'utf8mb4_general_ci',
  ];
  if (empty($settings['hash_salt'])) {
    $settings['hash_salt'] = getenv('DRUPAL_HASH_SALT');
  }
}
```

---

## Debugging Tips

- **"No Site Detected" but drush works** → Check `web_docroot: true` is in `pantheon.yml`. Drush specifies the path directly (`/code/web`) while nginx needs the docroot configured.
- **MCP access denied for anonymous** → Override `getAllowedRoles()` in the plugin; config-level roles alone won't work.
- **405 from nginx on POST** → Almost certainly the `web_docroot` issue above — requests aren't reaching Drupal at all.
- **Pantheon workflow failures** → Check with `ddev exec terminus workflow:list mcp-demo`
- **Watchdog logs on Pantheon** → `ddev exec terminus drush mcp-demo.dev -- watchdog:show --count=20`
- **Verify DB tables exist** → `ddev exec terminus drush mcp-demo.dev -- sqlq "SHOW TABLES"`
