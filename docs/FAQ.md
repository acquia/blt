# FAQ and Support

The following are common error messages and scenarios that our users have reported and common remedies. 

A general warning: BLT provides automation for numerous other applications including (but not limited to):

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

If you experience issues with a local BLT build, try using the included blt doctor command to diagnose your problem:

    blt doctor

If that isn't helpful, please post an issue on the [GitHub issue queue](https://github.com/acquia/blt/issues) including the following information:

- Your version of BLT, `composer info acquia/blt`
- Your operating system
- The **full** log output of your BLT command, wrapped in a [codeblock](https://help.github.com/articles/basic-writing-and-formatting-syntax/#quoting-code).

In seeking help, please keep the following points in mind:

* BLT is distributed under the GPLv2 license; WITHOUT ANY WARRANTY.
* The project maintainers are under no obligation to respond to support requests, feature requests, or pull requests.
* All contributions to BLT will be reviewed for compliance with Drupal Coding Standards and best practices as defined by the project maintainer.


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

### PHP Syntax Errors / Doctrine Errors
**Error Message:**
```
PHP Parse error:  syntax error, unexpected ':', expecting ';' or '{' in /var/www/<project>/vendor/doctrine/annotations/lib/Doctrine/Common/Annotations/AnnotationRegistry.php on line 50

Parse error: syntax error, unexpected ':', expecting ';' or '{' in /var/www/<project>/vendor/doctrine/annotations/lib/Doctrine/Common/Annotations/AnnotationRegistry.php on line 50
Drush command terminated abnormally due to an unrecoverable error.                                                                             [error]
Error: syntax error, unexpected ':', expecting ';' or '{' in
/var/www/<project>/vendor/doctrine/annotations/lib/Doctrine/Common/Annotations/AnnotationRegistry.php, line 50
[Acquia\Blt\Robo\Tasks\DrushTask]  Exit code 255  Time 10.708s
```

**Solution:**
This error frequently occurs when the PHP version committed in your composer file(s) differs from the PHP version on the system. Note: this error can effect:

* VMs
* Continuous Integration
* Cloud Environments

Ensure that all environments are running the same version of PHP. This can be accomplished by changing PHP versions on the systems, or by "locking" the PHP version in your composer file and re-running composer update.

Example of how to lock PHP version to PHP 5.6 in composer.json:
```
"config": {
  "platform": {
    "php": "5.6"
  }
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
