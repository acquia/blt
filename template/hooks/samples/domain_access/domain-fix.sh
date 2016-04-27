#!/usr/bin/env php
<?php
 
/**
 * @file domain_fix cloud hook for the Acquia Cloud
 * @author Adam Malone <adam.malone@acquia.com>
 *
 * This file should be placed in /hooks/common/post-db-copy
 * and will allow domains in domain_access module to be updated
 * following database copy. This ensures no manual updates to
 * the domains configuration are necessary after copying a db
 * between environments.
 */
 
$site = $argv[1];
$env = $argv[2];
$db = $argv[3];
$source = $argv[4];
 
// First ensure the domain module is installed. We do this by checking
// existence of the domain table in the appropriate database.
$domain=`echo "SHOW TABLES LIKE 'domain'" | drush @$site.$env ah-sql-cli --db=$db"`;
 
if (!$domain) {
  $returns[] = "Domain module not installed, aborting";
}
else {
 
  // Build a list of domains that require changing after the db has copied.
  // Each element of the $domains array should be keyed the machine name
  // and have key/value pairs of environment => URL.
  $domains = array(
    'foo_com' => array(
      'dev' => 'dev.foo.com',
      'test' => 'stg.foo.com',
      'prod' => 'foo.com',
    ),
      'bar_com' => array(
      'dev' => 'dev.bar.com',
      'test' => 'stg.bar.com',
      'prod' => 'bar.com',
    ),
    'example_com' => array(
      'dev' => 'dev.example.com',
      'test' => 'stg.example.com',
      'prod' => 'example.com',
    ),
  );
 
  // Iterate through the domains and update the record to the URL specified.
  // If the domain machine name does not exist, the record will be skipped.
  foreach ($domains as $domain => $info) {
    if (isset($info[$env])) {
      $to = $info[$env];
      $returns[] = "Updating domain table to update $domain to $to";
      echo "UPDATE domain SET subdomain = "$to" where machine_name = "$domain" | drush @$site.$env ah-sql-cli --db=$db";
    }
  }
}
 
// This output will be visible from the insights dashboard to reveal
// which domains have been updated.
foreach ($returns as $output) {
  print "$output\n";
}

