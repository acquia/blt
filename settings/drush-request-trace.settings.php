<?php

/**
 * @file
 * Traces drush requests in drupal-requests.log logs.
 */

/**
 * Adds logging information for drush requests in drupal-requests.log on Acquia.
 *
 * By default these requests show up with no REQUEST_METHOD or URI, which can
 * make splitting them up very hard.
 */

if (getenv('AH_SITE_ENVIRONMENT') && PHP_SAPI === 'cli') {
  // Set the `request method`.
  putenv('REQUEST_METHOD=CLI');
  // Set the `domain`.
  putenv('HTTP_HOST=' . $_SERVER['HTTP_HOST']);

  if (function_exists('drush_get_context')) {
    $cli_args = $GLOBALS['argv'];
    $cli_args[0] = 'drush';

    // Ensure each argument is wrapped in quotes.
    $cli_args = array_map(function ($value) {
      $escaped = escapeshellarg($value);
      return ("'$value'" === $escaped) ? $value : $escaped;
    }, $cli_args);

    // Prepare the uri.
    $uri = implode(' ', $cli_args);

    // Set the `request uri`.
    putenv('REQUEST_URI=' . $uri);
  }
}
