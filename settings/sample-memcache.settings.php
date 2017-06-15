<?php

/**
 * @file
 * Template for memcache configuration and SASL authentication for Acquia Cloud
 */

if ($is_ah_env) {
  $settings['cache']['default'] = 'cache.backend.memcache';
  $settings['memcache']['sasl'] = [
  'username' => 'user',
  'password' => 'password',
];
  // When using SASL, Memcached extension needs to be used
  // because Memcache extension doesn't support it.
  $settings['memcache']['extension'] = 'Memcached';
  $settings['memcache']['options'] = [
  \Memcached::OPT_BINARY_PROTOCOL => TRUE,
];
}
