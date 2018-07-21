<?php

/**
 * @file
 * Example implementation of ACSF post-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

// Use ACSF internal settings site flag to apply memcache settings.
if (getenv('AH_SITE_ENVIRONMENT') && 
	isset($site_settings['flags']['memcache_enabled']) &&
	isset($settings['memcache']['servers'])
) {
  require DRUPAL_ROOT . '/../vendor/acquia/blt/settings/memcache.settings.php';
}


