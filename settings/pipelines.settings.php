<?php

/**
 * @file
 * Pipelines environment specific settings.
 */

$databases = array(
  'default' => array(
    'default' => array(
      'database' => 'drupal',
      'username' => 'root',
      'password' => 'root',
      'host' => '127.0.0.1',
      'port' => '3306',
      'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
      'driver' => 'mysql',
      'prefix' => '',
    ),
  ),
);
