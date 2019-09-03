# How to get help

The following resources are available for support with BLT issues:

* [Acquia BLT Documentation](https://docs.acquia.com/blt/) on [docs.acquia.com](https://docs.acquia.com).
* [Acquia BLT Knowledge Base](https://support.acquia.com/hc/en-us/search#stq=Acquia+BLT&stp=1) on [support.acquia.com](https://support.acquia.com/hc/en-us).
* [Acquia BLT Github issue queue](https://github.com/acquia/blt/issues) (read [CONTRIBUTING.md](https://github.com/acquia/blt/blob/10.x/docs/CONTRIBUTING.md) before opening an issue or pull request on Github)
* Acquia Support via a Support Ticket or your Technical Account Manager
* Community support via the BLT channel in Drupal Slack

Read on for guidance in troubleshooting issues with the help of these resources.

## You might not have a BLT issue

BLT provides automation for numerous other applications including (but not limited to):

* Behat
* Composer
* Drupal 8
* Drupal VM
* Drush
* Git
* Gulp
* NPM / Yarn
* PHPCS
* PHPUnit

As a result, numerous "issues with BLT" are in fact "issues with one of the bundled applications." We strongly recommend a careful review of the errors presented with your project, which frequently direct you more appropriately to the underlying system that is the true cause (and not BLT itself).

## Basic troubleshooting

If you experience issues with a local BLT build, try using the included blt doctor command to diagnose your problem:

    blt doctor

If you are having problems with a specific command, run that command again with the `-vvv` argument (e.g. `blt setup -vvv`). This will provide verbose output and enumerate any underlying commands (Drush, Composer, etc) called by BLT.

If this identifies a specific command that is failing, try running that command directly (without invoking BLT). This will indicate whether you actually have a problem with BLT, or with another project such as Drush or Composer.

For instance, running `blt setup -vvv` may output:

```
...
 [drush] Changing working directory to: /Users/me/Sites/mysite/docroot
 [drush] Executing: /Users/me/Sites/mysite/vendor/bin/drush @blted10.local --site-name="BLTed 10" --site-mail="no-reply@acquia.com" --account-name="admin" --account-pass="admin" --account-mail="no-reply@acquia.com" --uri=default --yes --verbose site-install "lightning" "install_configure_form.update_status_module='array(FALSE,FALSE)'"
 Loaded alias @blted10.local from file
 ...
```

In this case, BLT is simply executing the following drush command for you:
```
/Users/me/Sites/mysite/vendor/bin/drush @blted10.local --site-name="BLTed 10" --site-mail="no-reply@acquia.com" --account-name="admin" --account-pass="admin" --account-mail="no-reply@acquia.com" --uri=default --yes --verbose site-install "lightning" "install_configure_form.update_status_module='array(FALSE,FALSE)'"
```
To debug the problem, run the drush command directly on the command line, as it will be easier to debug without involving BLT. If you still cannot diagnose the issue, contact Acquia Support. Do not open an issue in the BLT queue unless you’ve identified a specific bug or feature request for BLT itself.
