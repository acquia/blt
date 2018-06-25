<?php

/**
 * @file
 * Factory Hook: post-install.
 *
 * This hook enables you to execute PHP after a new website is created
 * in your subscription. Unlike most API-based hooks, this hook does not
 * take arguments, but instead executes the PHP code it is provided.
 *
 * This is used so that an ACSF site install will match a local BLT site
 * install. After a local site install, the update functions are run.
 *
 */

$site = $_ENV['AH_SITE_GROUP'];
$env = $_ENV['AH_SITE_ENVIRONMENT'];
$target_env = $site . $env;

// The public domain name of the website.
// Run updates against requested domain rather than acsf primary domain.
$domain = $_SERVER['HTTP_HOST'];

$domain_fragments = explode('.', $_SERVER['HTTP_HOST']);
$site_name = array_shift($domain_fragments);

exec("/mnt/www/html/$site.$env/vendor/acquia/blt/bin/blt drupal:update --environment=$env --site=$site_name --define drush.uri=$domain --verbose --yes");
