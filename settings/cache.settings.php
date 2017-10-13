<?php

/**
 * @file
 * Contains caching configuration.
 */

if ($is_prod_env || $is_stage_env) {
  $config['system.logging']['error_level'] = 'hide';
}
