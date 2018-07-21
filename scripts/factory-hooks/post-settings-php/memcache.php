<?php

/**
 * @file
 * Example implementation of ACSF post-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

// Use ACSF feature flag to indicate if a site should use memcache backend
if ($is_ah_env &&
  !empty($site_settings['flags']['memcache_enabled']) &&
  array_key_exists('memcache', $settings) &&
  array_key_exists('servers', $settings['memcache']) &&
  !empty($settings['memcache']['servers'])
) {
  require DRUPAL_ROOT . '/../vendor/acquia/blt/settings/memcache.settings.php';
}


