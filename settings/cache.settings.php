<?php

/**
 * @file
 * Contains caching configuration.
 */

if ($is_prod_env || $is_stage_env) {
  $config['system.logging']['error_level'] = 'hide';
}


/**
 * Example: Override required core defaults to use alternative cache backends.
 *
 * The bootstrap, discovery, and config bins use the chainedfast backend by
 * default and since the core service definition for these bins sets
 * the expire to CacheBackendInterface::CACHE_PERMANENT, these objects
 * are cached permanently and may result in stale configuration when
 * rebuilding the service container on deploymemnts and cache rebuilds.
 * Uncomment the relevant lines for each bin below and configure the desired
 * See https://www.drupal.org/node/2754947.
 */

// $settings['cache']['bins']['bootstrap'] = 'cache.backend.null';
// $settings['cache']['bins']['discovery'] = 'cache.backend.null';
// $settings['cache']['bins']['config'] = 'cache.backend.null';.
