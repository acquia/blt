BLT includes several components that can be used with PHPStorm:
1. An editor config file
2. A set of PHPCS rules and associated configuration
3. DrupalVM with Xdebug configuration

Here is the recommended setup process for new projects in PHPStorm. Note that these instructions were written for PHPStorm 2018.1, and may need to be adjusted for other versions.

## Setting up inspections

PHPStorm comes with a whole ton of built-in inspections for PHP. You can enable / disable each one individually in the UI. Most of these make sense in the context of Drupal / BLT projects, but not all.

To ensure consistent validation across all environments on your project, it's better to disable PHPStorm's built-in inspections and instead delegate code sniffing to Coder and PHPCodeSniffer, using the phpcs.xml rulesets that you distribute with your project (and that BLT provides by default).

Specifically:

1. Go to PHPStorm settings / preferences
1. Open Languages -> PHP -> Code Sniffer
1. Open the modal next to "Configuration" and select the PHPCS binary in your project located at vendor/bin/phpcs
1. Open Editor -> Inspections
1. Uncheck the PHP box to disable all PHP inspections
1. Recheck the box for undefined variable analysis (since Coder 2 doesn't support this)
1. Recheck the box next to PHP CodeSniffer validation
1. Under PHP CodeSniffer configuration, change the coding standard to "custom" and then select the phpcs.xml file in the root of your project

## Setting up Xdebug

For a tutorial on how to set up PHPStorm, Xdebug, and DrupalVM to work together, see https://danepowell.com/blog/xdebug-phpstorm-drupalvm
