<?php

/**
 * @file
 * Provo environment specific settings.
 */

$databases = [
  'default' => [
    'default' => [
      'database' => 'drupal',
      'username' => 'root',
      'password' => 'strongpassword',
      'host' => 'localhost',
      'port' => '3306',
      'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
      'driver' => 'mysql',
      'prefix' => '',
    ],
  ],
];
