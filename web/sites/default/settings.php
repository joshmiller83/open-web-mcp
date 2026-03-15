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
 * Pantheon environments.
 */
if (defined('PANTHEON_ENVIRONMENT')) {
  $databases['default']['default'] = [
    'driver'    => 'mysql',
    'database'  => $_ENV['DB_NAME'],
    'username'  => $_ENV['DB_USER'],
    'password'  => $_ENV['DB_PASSWORD'],
    'host'      => $_ENV['DB_HOST'],
    'port'      => $_ENV['DB_PORT'],
    'prefix'    => '',
    'collation' => 'utf8mb4_general_ci',
  ];

  if (empty($settings['hash_salt'])) {
    $settings['hash_salt'] = $_ENV['DRUPAL_HASH_SALT'];
  }

  $settings['file_public_path'] = 'sites/default/files';
  $settings['file_private_path'] = 'sites/default/files/private';
}

/**
 * Include DDEV-generated settings if present.
 */
if (file_exists($app_root . '/' . $site_path . '/settings.ddev.php')) {
  include $app_root . '/' . $site_path . '/settings.ddev.php';
}
