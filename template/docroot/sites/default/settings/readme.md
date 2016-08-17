# Settings files.

This directory contains modularized settings files. The are "required" into
a Drupal installation's sites/default/settings.php in the follow manner

    require_once '../all/settings/base.settings.php';
    require_once '../all/settings/cache.settings.php';
    // etc...
