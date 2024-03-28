# BLT

![BLT logo of stylized sandwich](https://github.com/acquia/blt/raw/11.x/docs/_static/blt-logo.png)

![Build Status](https://github.com/acquia/blt/actions/workflows/orca.yml/badge.svg?main) [![Packagist](https://img.shields.io/packagist/v/acquia/blt.svg)](https://packagist.org/packages/acquia/blt)

BLT (Build and Launch Tool) provides an automation layer for testing, building, and launching Drupal 8 and 9 applications.

**To learn more and get started, see the documentation: https://docs.acquia.com/blt**

**To review the Acquia and community provided plugins for BLT, see the [plugin registry](https://support-acquia.force.com/s/article/360046918614-Acquia-BLT-Plugins).**

## BLT End of Life

Acquia has announced the end of life for BLT. For more details, see https://github.com/acquia/blt/issues/4736

## BLT Versions

| BLT Version | Supported? | Major Drupal Version | PHP Version     | Drush Version |
|-------------|------------|----------------------|-----------------|---------------|
| 13.x        | Yes        | 9.x, 10.x            | 8.0, 8.1, 8.2 * | 11.x, 12.x *  |
| 12.x        | **No**     | 9.x                  | 7.4             | 10.x          |
| 11.x        | **No**     | 8.x                  | 7.4             | 9.x, 10.x     |

\* BLT users must upgrade to at least BLT 13.5.x to upgrade to PHP 8.1. and Drush 11.0.7 (or beyond).

PHP 8.2, Drush 12, and Drupal 10 support is unstable.

## Steps to use Acquia Drupal Recommended Settings with BLT.

- Update the BLT plugin to the latest release, which includes acquia/drupal-recommended-settings OOTB.
```
composer update acquia/blt -W
```

### Manual Process:

- Remove BLT reference from settings.php file located at `/docroot/sites/<site-name>/settings.php`.
```diff
- require DRUPAL_ROOT . "/../vendor/acquia/blt/settings/blt.settings.php";
- /**
-  * IMPORTANT.
-  *
-  * Do not include additional settings here. Instead, add them to settings
-  * included by `blt.settings.php`. See BLT's documentation for more detail.
-  *
-  * @link https://docs.acquia.com/blt/
-  */
+ require DRUPAL_ROOT . "/../vendor/acquia/drupal-recommended-settings/settings/acquia-recommended.settings.php";
+ /**
+  * IMPORTANT.
+  *
+  * Do not include additional settings here. Instead, add them to settings
+  * included by `acquia-recommended.settings.php`. See Acquia's documentation for more detail.
+  *
+  * @link https://docs.acquia.com/
+  */
```

- Update `default.local.settings.php` and `local.settings.php` to use the
  Environment Detector provided by this DSR plugin instead of BLT:
```diff
- use Acquia\Blt\Robo\Common\EnvironmentDetector;
+ use Acquia\Drupal\RecommendedSettings\Helpers\EnvironmentDetector;
```

### Automated Process:
- Use migrate command provided in BLT.
```
./vendor/bin/blt blt:migrate
```

# License

Copyright (C) 2020 Acquia, Inc.

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 2 as published by the Free Software Foundation.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
