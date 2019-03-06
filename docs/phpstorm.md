# PHPStorm

BLT includes several components that can be used with PHPStorm:
1. An editor config file
2. A set of PHPCS rules and associated configuration
3. DrupalVM with Xdebug configuration

Here is the recommended setup process for new projects in PHPStorm. Note that these instructions were written for PHPStorm 2018.1, and may need to be adjusted for other versions.

## Setting up inspections

PHPStorm comes with a whole ton of built-in inspections for PHP. You can enable / disable each one individually in the UI. Most of these make sense in the context of Drupal / BLT projects, but not all.

To ensure consistent validation across all environments on your project, it's better to disable PHPStorm's built-in inspections and instead delegate code sniffing to Coder and PHPCodeSniffer, using the _phpcs.xml_ rulesets that you distribute with your project (and that BLT provides by default).

Specifically:

1. From the main menu, go to PHPStorm -> Preferences...
1. Open Languages & Frameworks -> PHP -> Code Sniffer
1. Open the modal next to _Configuration_ and select the PHPCS binary in your project located at `vendor/bin/phpcs`
1. Open Editor -> Inspections
1. Uncheck the **PHP** box to disable all PHP inspections
1. Recheck the box for **Undefined -> Undefined variable analysis** (since Coder 2 doesn't support this)
1. Recheck the box next to **Quality tools -> PHP Code Sniffer validation**
1. Under _PHP CodeSniffer configuration_, change the coding standard to **custom** and then select the `phpcs.xml` file in the root of your project

## Setting up Xdebug

For a tutorial on how to set up PHPStorm, Xdebug, and DrupalVM to work together, see https://danepowell.com/blog/xdebug-phpstorm-drupalvm

Additionally, to support the PHPUnit testing and debugging instructions below, you can enable Xdebug for the CLI SAPI.

`php_xdebug_cli_disable: no`

## Setting up PHPUnit testing and debugging

To use PHPStorm, Xdebug, and DrupalVM together in a Drupal testing workflow, some additional configuration is required.

### Remote CLI Interpreter

1. Go to PHPStorm settings / preferences
1. Open Languages & Frameworks -> PHP
1. Open the modal next to _CLI Interpreter_
1. Click the _+_ to add a new interpreter and select **From Docker, Vagrant, VM, Remote...**
1. On the _Configure Remote PHP Interpreter_ dialog, Select **Vagrant** and set the **Vagrant Instance Folder** to the root of your project

### Debug

1. Go to PHPStorm settings / preferences
1. Open Languages & Frameworks -> PHP -> Debug
1. Recheck the box for **Force break at first line when no path mapping is specified** under _Xdebug_
1. Recheck the box for **Force break at first line when a script is outside the project** under _Xdebug_

### Servers

1. Go to PHPStorm settings / preferences
1. Open Languages & Frameworks -> PHP -> Servers
1. Click the _+_ to add a new sever configuration
1. Enter the host of your project in both the **Name** and **Host** fields, for example `local.my-project.com`

### Test Frameworks

1. Go to PHPStorm settings / preferences
1. Open Languages & Frameworks -> PHP -> Test Frameworks
1. Click the _+_ to add a new test framework configuration and select **PHPUnit by Remote Interpreter**
1. Choose the remote interpreter setup in a previous section of this guide
1. Under _PHP Unit library_ choose **Use Composer autoloader**
1. Open the modal next to _Path to script_ and select the autoloader file in your project at `vendor/bin/phpcs`
1. Under _Test Runner_ check **Default configuration file** and select the config file in your project as `docroot/core/phpunit.xml`

### Test Configurations

1. From the main menu, go to Run -> Edit Configurations...
1. Click the _+_ to add a new configuration and selet **PHPUnit**
1. Enter the host of your project in the **Name** field, for example `local.my-project.com`
1. Under _Test Runner_ choose **Defined in the configuration file**
1. Optionally, add any additional config or commands you would like to use for testing

### Plugins

Optionally, install and use https://github.com/mglaman/intellij-drupal-run-tests for an additional set test configurations specifically for Drupal.