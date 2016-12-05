# Best Practices

"How should I write code?"

_This document is a work in progress._ Unlinked items are planned topics, feel free to contribute.

## Standards

All work must conform to established best practices and coding standards. Code quality is ensured in a variety of ways:

* All code must conform to [Drupal Coding Standards](https://www.drupal.org/coding-standards). This is enforced via [local git hooks](../scripts/git-hooks/README.md) and code checks performed during continuous integration.
* All front end code must follow Drupal Theming Best Practices.
* All code must be reviewed by a peer or established integrator before being merged into the master branch.
* All new features must covered by an automated test that mirrors the ticket acceptance criteria.

## Exporting configuration

All site functionality should be represented in version-controlled code. This includes all configuration. Drupal configuration is typically exported via [Features](https://www.drupal.org/project/features).

### Features

Please see [Configuration Management](features-workflow.md) for Features best practices.

## Configuration updates

* If a change is happening, that change needs to be documented in code, preferably an update hook. E.g.,
    * Reverting features and feature components `features_revert_module()`
    * Enable / disable module `module_enable()`
    * Adding indexes to databases `db_add_index()`
* Updates needs to be actively monitored. This should be done using NewRelic, SumoLogic, Logstreaming, and/or other monitoring tools.
* Updates need to be intentional. E.g., don't use cloud hooks or cron jobs to automatically execute updates or clear caches.

## Caching

Without caching, Drupal is slow. As a general rule of thumb, _try to cache everything that you can_ and _try to invalidate that cache only when it is likely to be stale_.

Caching is complex. Because caching is so complex, it's difficult to provide general guidelines for caching strategies. Here are the factors the should be considered when making caching decisions:

* What is serving the cache? E.g., CDN, Varnish, Memcache, DB, APC, etc.
* What is being cached? An entire page, one component of a page, bytecode, etc.
* Is the cache context-aware? I.e., is there only one version of the cached data or might it differ depending circumstances? E.g., the "Welcome [username]" block changes depending on the logged-in user.
* What circumstances render the cached data stale? E.g., a cached view of press releases is stale when a press release is updated.
* How should the cache be invalidated? E.g., invalid after X minutes, triggered by a user action, etc.

Specifically, ensure that you are properly caching data at every level possible, including:

* Page caching (Varnish)
* Database query caching (Memcache)
* Views caching
* Block caching
* Panels caching
* Entity caching
* Twig caching
* APC / [Opcache](http://php.net/opcache) (Code caching)
* [Static caching](https://drupalwatchdog.com/volume-3/issue-2/drupal-static-caching)
* CDN (Content Delivery Network)

See the [Drupal 8 Cache API](https://www.drupal.org/developing/api/8/cache) documentation for information in implementing your caching strategy.

## Patching

All modifications to contributed code should be performed via a patch. For detailed information on how to patch projects, please see [patches/README.md](../template/patches/README.md)

## Views

Please see [views.md](views.md).

## Logging

* Any configuration changes from custom modules should be logged to watchdog (also [Acquia Library recommendations](https://docs.acquia.com/articles/how-audit-authenticated-user-actions-better-risk-management)
* Any destructive actions **must** be logged


## Building content types

@todo Document:

* Appropriate time to use fields
* Audit for overly complex content types
    * Reason: All fields loaded for each node load
    * Use case: Needs translations, user-facing form, revisions, etc.
