<?php

/**
 * Factory Hook: post-install
 *
 * This hook enables you to execute PHP after a new website is created
 * in your subscription. Unlike most API-based hooks, this hook does not
 * take arguments, but instead executes the PHP code it is provided.
 *
 */

$site=$_ENV['AH_SITE_GROUP'];
$env=$_ENV['AH_SITE_ENVIRONMENT'];

// The public domain name of the website. 
// HTTP host is used to run updates against the requested domain rather than the acsf primary domain. 
$domain=$_SERVER['HTTP_HOST'];


exec('/mnt/www/html/$target_env/vendor/acquia/blt/bin/blt deploy:update --define environment=$env --define drush.uri=$domain -v -y');