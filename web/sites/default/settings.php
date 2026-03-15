<?php

/**
 * @file
 * Drupal site configuration.
 *
 * This file will be included by the default Drupal settings scaffolding.
 * DDEV appends its own settings via settings.ddev.php (auto-included below).
 */

/**
 * Config sync directory.
 *
 * Point to the project-root config/sync directory so configuration stays
 * in version control rather than in the web-accessible files directory.
 */
$settings['config_sync_directory'] = '../config/sync';

/**
 * Include Pantheon-generated settings if present.
 */
if (file_exists('/var/www/html/web/sites/default/settings.pantheon.php')) {
  include '/var/www/html/web/sites/default/settings.pantheon.php';
}

/**
 * Include DDEV-generated settings if present.
 */
if (file_exists($app_root . '/' . $site_path . '/settings.ddev.php')) {
  include $app_root . '/' . $site_path . '/settings.ddev.php';
}
