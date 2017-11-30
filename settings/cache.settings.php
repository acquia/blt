<?php

/**
 * @file
 * Contains caching configuration.
 */

if ($is_prod_env || $is_stage_env) {
  $config['system.logging']['error_level'] = 'hide';
}


/**
 * Override static cache defaults in core.services.yml
 * 
 * Override the default backend set on the service definition on a per-bin basis. 
 * 
 * The bootstrap, discovery and config bins use the chainedfast backend by default and
 * since the core service definition for these bins sets the expire to 
 * CacheBackendInterface::CACHE_PERMANENT, these objects are cached permanetly by default
 * and may result in stale configuration whjen rebuilding the service container on deploymemnts 
 * and cache rebuilds. Uncomment the relevant bins below to change this behavior. 
 * See https://www.drupal.org/node/2754947
 */

 // $settings['cache']['bins']['bootstrap'] = 'cache.backend.null';
 // $settings['cache']['bins']['discovery'] = 'cache.backend.null';
 // $settings['cache']['bins']['config'] = 'cache.backend.null';