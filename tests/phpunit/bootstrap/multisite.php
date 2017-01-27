<?php

/**
 * @file
 * Properties file for multisite tests.
 */

$site = 'subsite';
$properties = [
  'site.name' => 'subsite',
  'site.uri.ci' => '127.0.0.1:8888',
  'site.drush.aliases.ci' => 'subsite.ci.local',
  'multisite.names' => 'default,subsite',
];
