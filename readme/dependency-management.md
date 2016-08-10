# Dependency Management

## Composer usage overview

Composer should be used to manage Drupal core, all contributed dependencies, and most third party libraries. The primary exception to this is front end libraries that may be managed via a front-end specific dependency manager, such as [Bower](http://bower.io/) or [NPM](https://www.npmjs.com/).

[Why do we use Composer](http://blog.nelm.io/2011/12/composer-part-1-what-why/) for dependency management? It is the dependency manager used by Drupal core.

Make sure to **familiarize yourself** with [basic usage](https://getcomposer.org/doc/01-basic-usage.md) of Composer, especially on how the [lock file](https://getcomposer.org/doc/01-basic-usage.md#composer-lock-the-lock-file) is used. In short: you should commit _both_ `composer.json` and `composer.lock` to your project, and every time you update `composer.json`, you must also run `composer update` to update `composer.lock`. You should never manually edit `composer.lock`.

**You should understand**:

* Why [dependencies should not be committed](https://getcomposer.org/doc/faqs/should-i-commit-the-dependencies-in-my-vendor-directory.md)
* The role of [composer.lock](https://getcomposer.org/doc/01-basic-usage.md#composer-lock-the-lock-file)
* How to use [version constraints](https://getcomposer.org/doc/articles/versions.md)
    * [Why using unbound version constraints is a bad idea](https://getcomposer.org/doc/faqs/why-are-unbound-version-constraints-a-bad-idea.md)
* [The difference](http://stackoverflow.com/questions/16679589/whats-the-difference-between-require-and-require-dev) between `require` and `require-dev`

### Recommended tools and configuration

* Globally install pretissimo for parallelized composer downloads:

        composer global require "hirak/prestissimo:^0.3"

* If you have xDebug enabled for your PHP CLI binary, it is highly recommended that you disable it to dramatically improve performance.

### Contributed projects and third party libraries

All contributed projects hosted on drupal.org, including Drupal core, profiles, modules, and themes, can be found on [Drupal packagist](https://packagist.drupal-composer.org/). Most non-Drupal libraries can be found on [Packagist](http://packagist.com/). For any required packaged not hosted on one of those two sites, you can define your own array of [custom repositories](https://getcomposer.org/doc/05-repositories.md#repository) for Composer to search.

Note that Composer versioning is not identical to drupal.org versioning.

### Resources

* [Composer Versions](https://getcomposer.org/doc/articles/versions.md) - Read up on how to specify versions.
* [Drupal packagist site](https://packagist.drupal-composer.org/) - Find packages and their current versions.
* [Drupal packagist project](https://github.com/drupal-composer/drupal-packagist) - Submit issues and pull requests to the engine that runs Drupal packagist.
* [Drupal packagist project](https://github.com/drupal-composer/drupal-packagist) - Submit issues and pull requests to the engine that runs Drupal packagist.
* [Drupal Composer package naming conventions](https://www.drupal.org/node/2471927)
* [Packagist](http://packagist.com/) - Find non-drupal libraries and their current versions.

## Add dependencies

To add a new package to your project, use the `composer require` command. This will add the new dependency to your `composer.json` and `composer.lock` files, and download the package locally. E.g., to download the pathauto module run,

        composer require drupal/pathauto

Commit `composer.json` and `composer.lock` afterwards.

## Update dependencies (core, profile, module, theme, libraries)

To update a single package, run `composer update [vendor/package]`. E.g.,

        composer update drupal/pathauto

To update all packages, run `composer update`.

Commit `composer.json` and `composer.lock` afterwards.

## Remove dependencies

To remove a package from your project, use the `composer remove` command:

        composer remove drupal/pathauto

Commit `composer.json` and `composer.lock` afterwards.

## Patch a project

Please see [patches/README.md](../patches/README.md) for information on patch naming, patch application, and patch contribution guidance.
