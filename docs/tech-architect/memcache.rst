.. include:: ../../common/global.rst

Memcache
========

The following documentation describes how to configure the `Memcache API and
Integration <https://www.drupal.org/project/memcache>`__ (Memcache)
module for Drupal 8. The Memcache module provides an API for using
Memcached and the PECL Memcache or Memcached libraries with Drupal and
provides backends for Drupal's caching and locking systems. The most
complete and up to date documentation is included with the module, in
the `README.txt
<http://cgit.drupalcode.org/memcache/tree/README.txt?h=8.x-2.x>`__
file.

Before enabling the Memcache module, it is important to understand how
the Drupal 8 `Cache API
<https://api.drupal.org/api/drupal/core%21core.api.php/group/cache/8.4.x>`__
functions and the how Drupal determines which cache back end to use for a
specific cache bin, see `New cache back end configuration order
<https://www.drupal.org/node/2754947>`__.

.. note::

   Drupal 8 does not handle configurations where a given cache back end is set
   as default, but the module providing the back end is not enabled. See
   `this article on Drupal.org
   <https://www.drupal.org/node/2766509>`__ for more information.

The snippets below provide logic that allows for using memcache as a cache
back end if the memcached extension is available and the Drupal module exists
in the codebase but is not yet enabled. Using memcache as a cache back end
negates patching core for graceful fallback and allows for purging stale cache
objects when the service definition container is updated on website install or
deployments. This allows for using alternative cache bins such as memcache on
website install and deployments as needed. Using alternative cache bins such
as memcache can help resolve website installation and deploy issues caused by
cache race conditions. Cache race conditions are common on multisite
applications using the `Content translation
<https://www.drupal.org/docs/8/core/modules/content-translation>`__ module
where the service container contains negotiation methods that override a
locked default language on website install.


.. _blt-memcache-ac:

Acquia Cloud
------------

:doc:`/acquia-cloud/performance/memcached/` provides detailed information
regarding how Acquia supports Memcached for its subscriptions and products,
and is a good resource in general for information regarding Drupal and
Memcache integrations. It is important that the settings for
``memcache_key_prefix`` and ``memcache_servers`` not be modified on
Acquia Cloud.

|acquia-product:blt| modifies the Memcache module integration on Acquia Cloud.
|acquia-product:blt|'s configuration explicitly overrides the default bins for
the discovery, bootstrap, and configuration cache bins because Drupal core
permanently caches these static bins by default. This is required for
rebuilding service definitions accurately on cache rebuilds and deploys. See
`caching.settings.php
<https://github.com/acquia/blt/blob/10.x/settings/cache.settings.php>`__.


.. _blt-memcache-acsf:

Acquia Cloud Site Factory
-------------------------

As of |acquia-product:blt| 9.2, the factory hooks contain the necessary
code to handle memcache integration with Acquia Cloud Site Factory provided
that your subscription and hardware are properly configured.
:doc:`/acquia-cloud/performance/memcached/` provides more information.

If you are upgrading from a previous version of |acquia-product:blt| to 9.2.x,
make sure and re-generate your factory hooks using:

.. code-block:: text

   recipes:acsf:init:hooks

The preceding command will create a new memcache factory hook for use on
Acquia Cloud Site Factory.


.. _blt-memcache-local-dev:

Local Development
-----------------

The below has been tested with DrupalVM as configured through
|acquia-product:blt|'s ``blt vm`` command, but should also work for most
CI environments where the memcache back end is ``localhost`` on port ``11211``.

Add the below statements to an environment's ``local.settings.php`` to
use memcache as the default back end for Drupal's caching and locking
systems. The memcache module does not need to be enabled with the
snippet below, but may need to be if this configuration is removed. Note
that the below configuration explicitly overrides the default bins for
the discovery, bootstrap, and configuration cache bins because Drupal core
permanently caches these static bins by default.

.. code-block:: text

   // Include a unique prefix for each local Drupal installation.
   if ($is_local_env) {
     $settings['memcache']['key_prefix'] = $site_dir;
   }

   require DRUPAL_ROOT . "/../vendor/acquia/blt/settings/memcache.settings.php";

.. Next review date 20200423
