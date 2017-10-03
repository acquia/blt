<?php

/**
 * @file
 * Provo environment specific settings.
 */

$databases = array(
  'default' => array(
    'default' => array(
      'database' => 'drupal',
      'username' => 'root',
      'password' => 'strongpassword',
      'host' => 'localhost',
      'port' => '3306',
      'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
      'driver' => 'mysql',
      'prefix' => '',
    ),
  ),
);
