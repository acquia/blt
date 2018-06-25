<?php
/**
 * @file
 * Example implementation of ACSF post-sites-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */
// The function_exists check is required as the file is included several times.
if (!function_exists('gardens_data_get_sites_from_file')) {
  /**
   * Get the domains defined for the given site.
   *
   * @param string $name
   *   The technical ACSF site name to filter on.
   * @param $reset
   *   Whetever to reset APC cache or not.
   *
   * @return array|mixed|null
   *   An array of sites indexed by domain that matches the given site name.
   */
  function gardens_data_get_sites_from_file($name, $reset = FALSE) {
    static $domains = NULL;
    if (!isset($domains)) {
      $cid = 'domains-' . $name;
      if (
        !$reset
        && GARDENS_SITE_DATA_USE_APC
        && ($data = gardens_site_data_cache_get($cid)) !== FALSE
      ) {
        $domains = $data;
      }
      elseif ($map = gardens_site_data_load_file()) {
        $domains = array_filter(
          $map['sites'],
          function($item) use ($name) {
            return $item['name'] == $name;
          }
        );
        if (GARDENS_SITE_DATA_USE_APC) {
          gardens_site_data_cache_set($cid, $domains);
        }
      }
      else {
        $domains = [];
      }
    }
    return $domains;
  }
}
// Get all the domains that are defined for the current site.
$domains = gardens_data_get_sites_from_file($data['gardens_site_settings']['conf']['acsf_db_name']);
// Get the site's name from the first domain.
global $acsf_site_name;
$acsf_site_name = explode('.', array_keys($domains)[0])[0];