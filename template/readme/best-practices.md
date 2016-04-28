# Best Practices

"How should I write code?"

_This document is a work in progress._ Unlinked items are planned topics, feel free to contribute.

* [Standards](#standards)
* [Exporting configuration](#exporting-config)
* [Configuration updates](#config-updates)
* [Caching strategies](#caching)
* [Patching](#patching)
* [Building Views](#views)
* [Logging](#logging)
* [Building content types](#content-types)

## <a name="standards"></a>Standards

All work must conform to established best practices and coding standards. Code quality is ensured in a variety of ways:

* All code must conform to [Drupal Coding Standards](https://www.drupal.org/coding-standards). This is enforced via [local git hooks](../scripts/git-hooks/README.md) and [code checks](../build/ performed during continuous integration.
* All front end code must follow Drupal Theming Best Practices.
* All code must be reviewed by a peer or established integrator before being merged into the master branch.
* All new features must covered by an automated test that mirrors the ticket acceptance criteria.

Please peruse the [examples](examples) directory for examples of various coding best practices.

## <a name="exporting-config"></a>Exporting configuration

All site functionality should be represented in version-controlled code. This includes all configuration. Drupal configuration is typically exported via [Features](https://www.drupal.org/project/features). 

### Features

Features should generally follow the [KIT Feature specifications](https://www.drupal.org/project/kit).

Notable principles from this specification include:

- A feature should provides a collection of Drupal entities which taken together satisfy a certain use case. Example:a gallery feature provides a gallery view, a photo content type, and several blocks allowing a user to add new photos and browse those submitted by others.
- A feature must, to the best of the creator's knowledge, use a unique code namespace.

Additional guidelines include:

* Each feature should be as discrete and independent as possible. Feature dependencies should be carefully considered! All dependencies should be one-way. Circular dependencies should never exist.

Exceptions:

* Distribution compliance guidelines are only applicable for distributions
* Sites will often need to contain a single "sitewide" feature that defines global configuration and is required by all other features. This can be viewed as a "core" feature but should not be abused as a dumping ground for miscellany.

Common mistakes:

* Creation of large features that encompass many un-related components. E.g., multiple un-related content types, views, roles, permissions are all bundled into a single feature.
* Poor design of dependencies, e.g., creation of circular dependencies.
* Each feature has too many dependencies, so no one feature can be used in isolation, or a single feature becomes impossible to disable. E.g., the "Workflow" feature depends on the "Press Room" feature because it requires field_body which is provided by "Press Room."
* Features are too granular. E.g., there is a separate feature for each role, a feature contains only a single view, etc.

## <a name="config-updates"></a>Configuration updates

* If a change is happening, that change needs to be documented in code, preferably an update hook. E.g.,
    * Reverting features and feature components `features_revert_module()`
    * Enable / disable module `module_enable()`
    * Adding indexes to databases `db_add_index()`
* Updates needs to be actively monitored. This should be done using NewRelic, SumoLogic, Logstreaming, and/or other monitoring tools.
* Updates need to be intentional. E.g., don't use cloud hooks or cron jobs to automatically execute updates or clear caches.

## <a name="caching"></a>Caching

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

## <a name="patching"></a>Patching

All modifications to contributed code should be performed via a patch. For detailed information on how to patch projects, please see [../patches/README.md]
(../patches/README.md)

## <a name="views"></a>Views

Please see [views.md](views.md).

## <a name="logging"></a>Logging

* Any configuration changes from custom modules should be logged to watchdog (also [Acquia Library recommendations|https://docs.acquia.com/articles/how-audit-authenticated-user-actions-better-risk-management])
* Any destructive actions **must** be logged


## <a name="content-types"></a>Building content types

@todo Document:
* Appropriate time to use fields
* Audit for overly complex content types
    * Reason: All fields loaded for each node load
    * Use case: Needs translations, user-facing form, revisions, etc.
