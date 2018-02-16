<?php

/**
 * @file
 * GitLab environment specific settings.
 */

$databases = array(
  'default' => array(
    'default' => array(
      'database' => 'drupal',
      'username' => 'drupal',
      'password' => 'drupal',
      'host' => 'mysql',
      'port' => '3306',
      'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
      'driver' => 'mysql',
      'prefix' => '',
    ),
  ),
);
