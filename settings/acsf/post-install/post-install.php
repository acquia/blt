<?php

/**
 * @file
 * Factory Hook: post-install.
 *
 * This hook enables you to execute PHP after a new website is created
 * in your subscription. Unlike most API-based hooks, this hook does not
 * take arguments, but instead executes the PHP code it is provided.
 *
 * @todo Remove this script after https://www.drupal.org/node/2752961 is 
 * resolved and the corresponding Drupal core version is set as a minimum 
 * requirement for BLT.
 */

$site = $_ENV['AH_SITE_GROUP'];
$env = $_ENV['AH_SITE_ENVIRONMENT'];

// The public domain name of the website.
// Run updates against requested domain rather than acsf primary domain.
$domain = $_SERVER['HTTP_HOST'];


exec("/mnt/www/html/$target_env/vendor/acquia/blt/bin/blt deploy:update --define environment=$env --define drush.uri=$domain --verbose --yes");
