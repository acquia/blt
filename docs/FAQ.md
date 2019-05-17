# FAQ and Support

Before opening an issue, make sure to thoroughly review this document and search the remainder of BLT's documentation for guidance related to your issue.

Also make sure to search the issue queue (including CLOSED issues) for information that might be relevant.

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
To debug the problem, just run the drush command directly on the command line. It may be easier to navigate without BLT. Once the problem is resolved, go back to using BLT's automation layer.

## Common BLT Issues and Solutions

The following are common error messages and scenarios that our users have reported and common remedies.


### BLT Command Failure (generic)

**Error Message:**
```
You must run this command from within a BLT-generated project repository.
```

**Solution:**

If you have trouble using the blt alias, make sure it’s installed correctly and then restart your terminal session:
```
composer run-script blt-alias
source ~/.bash_profile
```

### PHP Syntax Errors / missing extensions
**Error Message:**
This manifests as errors that occur only in certain environments, often syntax errors or errors about missing extensions:
```
PHP Parse error:  syntax error, unexpected ':', expecting ';' or '{' in /var/www/<project>/vendor/doctrine/annotations/lib/Doctrine/Common/Annotations/AnnotationRegistry.php on line 50

Parse error: syntax error, unexpected ':', expecting ';' or '{' in /var/www/<project>/vendor/doctrine/annotations/lib/Doctrine/Common/Annotations/AnnotationRegistry.php on line 50
Drush command terminated abnormally due to an unrecoverable error.                                                                             [error]
Error: syntax error, unexpected ':', expecting ';' or '{' in
/var/www/<project>/vendor/doctrine/annotations/lib/Doctrine/Common/Annotations/AnnotationRegistry.php, line 50
[Acquia\Blt\Robo\Tasks\DrushTask]  Exit code 255  Time 10.708s
```

**Solution:**
This error occurs when Composer packages were installed on a machine running a newer PHP version than the environment exhibiting the error, leading to syntax errors or errors due to missing extensions.

This error can affect VMs, continuous integration environments, and cloud environments.

Ensure that all environments are running the same version of PHP. This can be accomplished by changing PHP versions on the systems, and by "locking" the PHP version in your composer file and re-running composer update.

Example of how to lock PHP version to PHP 7.2 in composer.json:
```
"require": {
  "php": "7.2"
},
```

### Robo Default Config Error
**Error Message:**
```
PHP Notice:  Undefined property: Acquia\Blt\Robo\Config\DefaultConfig::$config in /var/www/vendor/acquia/blt/src/Robo/Config/DefaultConfig.php on line 70
```

**Solution:**
Update to a more recent version of BLT OR manually pin Robo in your composer.json file.

```
"consolidation/robo": "~1.2.4"
```

### CI Errors

**Issue**
Errors appearing on TravisCI which are not replicable on local or other environments.

**Solution**
[TravisCI has an internal caching feature](https://docs.travis-ci.com/user/caching) which can help speed up builds. At times, though, this cache results in semi-baffling build failures which cannot be replicated elsewhere. In these instances, the solution is sometimes simply to [clear Travis's cache](https://docs.travis-ci.com/user/caching/#Clearing-Caches).

### Permission Denied During SQL Sync / BLT Sync

**Issue**
During commands such as drush sql-sync, blt sync, or blt sync:refresh, the command errors out with output similar to:

```php
sh: 1: cannot create auto.gz: Permission denied
 [error]  Database dump failed [3.1 sec, 8 MB]
```

This issue was originally documented on the BLT side in [issue #2641](https://github.com/acquia/blt/issues/2641).

**Solution**
This is *most* likely an issue of Drush version mismatches between environments. If you are running Drush 9 locally but Drush 8 in your remote environment, you will encounter this issue.

It has been documented by the [Drush team](https://github.com/drush-ops/drush/releases/tag/9.2.1).

Option 1: Deploy Drush 9 to the remote environment.

Option 2: Temporarily add a ```--source-dump``` option per the Drush docs during the sql-sync command.

### Dirty source directory prevents deploys

**Issue**
When trying to deploy code, you get the following error:
> There are uncommitted changes, commit or stash these changes before deploying

**Background**
Before deploying code, BLT ensures that the source directory is clean according to Git. This ensures that any changes being deployed are captured in your source repository. This is especially important in a CI environment in order to ensure that nothing during the testing process itself modified the codebase in way that could lead to undefined or undesirable behavior once deployed. For instance, this prevents the testing process from changing database credentials that then get deployed to a production environment.

**Solution**
Ensure that your Git directory is clean before deploying. BLT should print a list of all dirty files to help you debug. If deploying locally, this is simply a matter of committing the changes. If deploying via CI, you'll need to determine what might be causing these files to change during the test process.

A few examples of what can cause files to change during the deploy process and how to troubleshoot:
- If you have defined frontend build steps that call `npm install`, `package-lock.json` may be modified during deployments. Try using `npm ci` instead (see [Frontend docs](frontend.md) for details). Warning: `npm ci` is not present in older versions of npm.
- Ensure that the files' permissions are committed properly (as git will track a file as `M` if the diff is the same, but permissions differ)
- Try replicating the CI process locally by running the same commands (visible in the CI logs), such as `blt setup` and `blt tests:all`. If these change files locally, you should determine if these changes need to be committed or whether your test scripts need to be adjusted to avoid creating changes.
- Run `blt doctor` locally to ensure that there are no problems such as missing settings file includes.
- See [this issue](https://github.com/acquia/blt/issues/3564) for additional documentation and solutions.

In an emergency, you can disable this check by passing the `--ignore-dirty` flag to `blt deploy`, but this is strongly discouraged as it may conceal deeper issues with your codebase.
