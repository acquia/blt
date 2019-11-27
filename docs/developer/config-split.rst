
.. include:: ../../common/global.rst

Configuration split
===================

Config split is the *standard* Configuration Management Strategy provided by
Acquia BLT. Acquia BLT does support other options for configuration management
(or none), see :doc:`/blt/developer/configuration-management/`.

For more information, review `Managing Configuration with Config Split
<https://support.acquia.com/hc/en-us/articles/360024009393>`__.


.. _blt-config-splits-scenario:

Example Scenarios
-----------------

Assume there is a *kitchen sink* website requiring all the preceding types of
splits. The website is a multisite application and it will be hosted in
several environments. The various environments must share some default
configuration between all sites. The environments must allow some features,
such as blog enablement, on some sites and not others.


.. _blt-config-split-default-config:

Default configuration
---------------------

Start by exporting the default configuration for the application. The default
configuration is the configuration imported for your application, by default,
even if no splits are defined or active. The default configuration will be
shared by all websites using the application.

For the sake of the tutorial, let's focus on one configuration setting:
``system.performance`` The ``system.performance`` setting controls caching and
aggregation settings for Drupal core.

#.  Navigate to ``/admin/config/development/performance``, and then enable
    caching and aggregation.
#.  Run the following command:

    .. code-block:: bash

        drush en config_split -y

#.  Run the following command:

    .. code-block:: bash

        drush config-export -y

#.  Run the following command:

    .. code-block:: bash

        drush cr

Running the preceding drush commands will populate ``../config/default`` with
all configuration for the website. The configuration setting
``../config/default/system.performance.yml`` now exists and contains the
following configuration (a partial representation):

.. code-block:: text

   cache:
     page:
      max_age: 3600
   css:
     preprocess: true
     gzip: true
   js:
     preprocess: true
     gzip: true


.. _blt-test-overriding-reverting:

Test overriding and reverting
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can test the process of importing configuration by completing the
following steps:

#.  Navigate to ``/admin/config/development/performance``.
#.  Disable caching and aggregation.
#.  Run the following command:

    .. code-block:: bash

       drush config-import

Caching and aggregation will be re-enabled, congruent with the prior
exported configuration.

The preceding example is the simplest use case for the configuration
management system.


.. _blt-environment-split:

Environment split
-----------------

By default, you want caching, CSS, and JavaScript aggregation enabled
on all environments, but you want the local environment to be an exception.
You must disable caching and aggregation on local machines to speed up the
development process.

To create the local environment exception, you will create a ``local``
configuration split. The following command ``blt recipes:config:init:splits``
will create the local configuration split, and automate your other environment
splits. To create the "local" split manually, complete the following process:

#. Run the following command:

   .. code-block:: bash

      mkdir -p ../config/envs/local

#. Navigate to
   ``/admin/config/development/configuration/config-split/add``

#. In the user interface, enter values for the following fields:

   -  label: ``Local``
   -  folder: ``../config/envs/local``
   -  Navigate to **Conditional Split > Configuration items > Select**
      ``system.performance``

#. Save your field changes.

#. Run the following command:

   .. code-block:: bash

        drush config-export -y

   The Drush command will export the configuration definition for the split
   itself, which is stored in
   ``config/default/config_split.config_split.local.yml``. The file must
   contain the following settings:

   .. code-block:: text

       uuid: ...
       langcode: en
       status: true
       dependencies: {  }
       id: local
       label: Local
       folder: ../config/envs/local
       module: {  }
       theme: {  }
       blacklist: {  }
       graylist:
         - system.performance
       graylist_dependents: true
       graylist_skip_equal: true
       weight: 0

#. Run the following command:

   .. code-block:: bash

      drush cr

   This Drush command allows the configuration split to recognize the local
   split is active. You rely on Acquia BLT to display the split as active on
   local computers using a `settings.php include
   <https://github.com/acquia/blt/blob/9.x/settings/config.settings.php#L22>`__.

With your ``local`` split ready, continue the following process:

#. Navigate to ``/admin/config/development/performance`` and disable
   caching and aggregation.

#. Run the following command:

   .. code-block:: bash

      drush csex

   With the local split being active, the Drush command will export the local
   split ``system.performance`` settings to
   ``../config/envs/local/system.performance.yml``, and must contain
   the following configuration:

   .. code-block:: text

      cache:
      page:
        max_age: 0
      css:
      preprocess: false
      gzip: false
      js:
      preprocess: false
      gzip: false


.. _blt-suggested-env-splits:

Supported environment splits
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Acquia BLT has built-in support for the following environment splits:

.. list-table::
   :widths: 20 50 30
   :header-rows: 1
   :class: verticaltable

   * - Split
     - Environment
     - File path

   * - ``local``
     - Any non-Acquia, non-Travis environment
     - ``../config/envs/local``

   * - ``ci``
     - Acquia Cloud pipelines feature or Travis CI
     - ``../config/envs/ci``

   * - ``dev``
     - Acquia Dev environment
     - ``../config/envs/dev``

   * - ``stage``
     - Acquia Staging environment
     - ``../config/envs/stage``

   * - ``prod``
     - Acquia Prod environment
     - ``../config/envs/prod``

   * - ``ah_other``
     - Any Acquia environment not listed here
     - ``../config/envs/ah_other``

Acquia BLT will mark only the preceding splits as enabled *if they exist*,
and won't create the splits for you.


.. _blt-settings-notes:

.. note::

   -  The folder is relative to the Drupal docroot.

   -  You configure active to zero because you don't want configuration
      management to manage whether the split is active. Instead, you will
      rely on Acquia BLT to enable the split, when appropriate, through a
      `settings.php include
      <https://github.com/acquia/blt/blob/9.x/settings/config.settings.php#L22>`__.
      If you are using Acquia BLT, the ``include`` must load for you as a
      consequence of including ``blt.settings.php`` in your ``settings.php``
      file. You may override the logic by configuring ``$split`` in
      ``settings.php`` before including ``blt.settings.php``.

   -  Even on your local environment, after running ``drush config-import``,
      the local configuration split has a status of ``active (overwritten)``.
      The status is normal and doesn't point to a problem. The fact the local
      configuration split is active is an override of the exported
      ``active: 0`` setting in the split itself. It doesn't necessarily mean
      the configuration which the split controls is actually overridden.


.. _blt-feature-split:

Feature split
-------------

Consider you are creating a multisite Drupal application. You want Site A
and Site B to have blogs, and Site C to have no blog. You must manage the blog
feature itself through configuration management.

To create the feature split, you will create a *blog* configuration split
active on Site A and Site B, but not on Site C.


.. _blt-creating-feature-split:

Creating a feature split
------------------------

#. Create a ``blog`` content type.

#. Run the following command:

   .. code-block:: bash

        mkdir -p ../config/features/blog

#. From ``/admin/config/development/configuration/config-split/add``,
   add the following code:

   .. code-block:: text

      status: false
      label: Blog
      folder: ../config/features/blog
      blacklist:
        - core.base_field_override.node.blog_entry.promote
        - core.entity_form_display.node.blog_entry.default
        - core.entity_view_display.node.blog_entry.default
        - core.entity_view_display.node.blog_entry.teaser
        - field.field.node.blog_entry.body
        - node.type.blog_entry
        - system.action.user_add_role_action.blog_entry_creator
        - system.action.user_add_role_action.blog_entry_reviewer
        - system.action.user_remove_role_action.blog_entry_creator
        - system.action.user_remove_role_action.blog_entry_reviewer
        - user.role.blog_entry_creator
        - user.role.blog_entry_reviewer
      graylist: {  }
      graylist_dependents: true
      graylist_skip_equal: true
      weight: 0

#. Visit ``/admin/config/development/configuration/ignore``, and then add the
   following line to **Configuration entity names to ignore**:

   .. code-block:: text

      config_split.config_split.blog:status

   The preceding code will instruct the configuration management system to
   ignore the status of the blog configuration split. Ignoring the status will
   permit you to export a default status of ``false`` for the blog split, and
   still manually enable the split on selected sites without flagging the
   split as overwritten.

#. Run the following command:

   .. code-block:: bash

        drush config-export -y

   The configuration for the blog split itself must now exist in
   ``../config/default/config_split.config_split.blog.yml``.

#. Run the following command:

   .. code-block:: bash

        drush cr


.. _blt-enabling-feature-split:

Enabling a feature split
------------------------

Assume you want to enable the blog split for multisite Site 2, to enable
the blog feature, complete the following steps:

#.  Visit ``/admin/config/development/configuration/config-split`` and
    enable **Blog split**.
#.  Import configuration for Site2 by running the following command:

    .. code-block:: bash

        drush config-import --uri=site2


.. _blt-feature-split-issues:

Issues with the feature split approach
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

-  The status of the feature split isn't managed through configuration
   management. You must enable the split by using the user interface even on
   a production environment.

-  It can be difficult to identify all the configuration a given feature
   must encompass.

-  It's not possible to disentangle a single feature from all related
   configuration. For instance, you may segment the node configuration and
   fields for the *Blog* feature, but
   ``config/default/search_api.index.content.yml`` and
   ``config/default/views.view.search.yml`` still contain references to
   ``blog_entry`` in their exported configuration. These references aren't
   necessarily problematic, but can be messy.


.. _blt-multisite-split:

Multisite split
---------------

Consider if you want your website to have different cache lifetimes, then the
default configuration specifies. You have the following two directories:

-  ``docroot/sites/default``
-  ``docroot/sites/site2``


.. _blt-creating-multisite-split:

Creating a multisite split
~~~~~~~~~~~~~~~~~~~~~~~~~~

#. Run the command ``mkdir -p ../config/site2`` to ensure you have the
   following configuration directories:

   -  ``config/default``
   -  ``config/site2``

#. Create a split for Site 2:

   .. code-block:: text

      status: false
      label: Site 2
      folder: ../config/site2
      blacklist: {  }
      graylist: {  }
      graylist_dependents: true
      graylist_skip_equal: true
      weight: 0


.. _blt-running-commands-against-multisites:

Running commands against multisites
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

-  When running a Drush command against a multisite, include the
   ``uri`` option. For example:

   .. code-block:: bash

        drush --uri=site2

-  When running an Acquia BLT command against a multisite, include the
   website configuration value. For instance,
   ``blt setup --define site=site2``. Acquia BLT also allows you to create
   website-specific configuration. For more information, see
   :doc:`/blt/tech-architect/multisite/`.


.. _blt-profile-split:

Profile split
-------------

.. tabs::

   .. tab:: 10.x

      Acquia BLT does not supports install profile- (or just profile-) based
      splits. The recommended workflow is to use the `Profile Split Enable
      <https://github.com/nedsbeds/profile_split_enable>`__ module to support
      this capability.

   .. tab:: 9.2.x

      If you are using multisite, you may want to use several installation
      profiles for your application. Acquia BLT will evaluate if a split
      exists having the same name as your active installation profile.

      For example, if a given website on your application uses the
      ``lightning`` profile, Acquia BLT configures the ``lightning``
      configuration split to active, if the split exists. Typically, you
      store profile splits in ``config/profiles/[profile_name]``.


.. _blt-misc:

Miscellaneous
-------------

Exporting to an inactive split
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When developing locally, you often must export to a split other than
local. For instance, you may want to change some of the configuration in
the dev split by using the following methods:

-  Manually changing the configuration files is the most straightforward,
   but time-consuming method.
-  Using the ``drush config-split-export [split]`` command to export to a
   specific split. For instance, to export the current configuration on
   your local server to the dev split, you must run:
   ``drush config-split-export dev``


.. _blt-conflicting-config:

Conflicting configuration
~~~~~~~~~~~~~~~~~~~~~~~~~

*Greater weight takes precedence*: Where possible, you must avoid exporting
the same configuration (with different values) to several splits. Exporting
the same configuration is sometimes desirable. When two splits define the same
configuration, the split with the greater weight will take precedence. The
logic is counterintuitive, as the common Drupal convention is for elements
with lesser weights to take precedence.

*Several splits blacklist the same configuration*: If you want to export
blacklisted configuration in more than one split, then you must use the
``drush config-split-export [split]`` commands and specify the split to
which you would like to export the configuration.


.. _blt-config-split-terminology:

Terminology
~~~~~~~~~~~

*Complete split (blacklist)*: Blacklisted splits are blacklisted from
``config/default``. If a given split is active, and the split defines a
configuration setting in its blacklist, the configuration setting won't
export to ``config/default`` when ``drush config-export`` runs:

-  Exported to split
-  *Not* exported to default configuration

*Conditional split (graylist)*: Graylist splits allow a given configuration
setting to export to both the default configuration and also to a split's
configuration (overriding default when active):

-  Exported to split
-  Also exported to default configuration

Graylists are also used for configuration intended to be unlocked in
production (such as webforms). If you want to customize the behavior, you
can use the graylist feature described in `Advanced Drupal 8 Configuration
Management (CMI) Workflows
<https://blog.liip.ch/archive/2017/04/07 advanced-drupal-8-cmi-workflows.html>`__.


.. _blt-config-split-dev-settings:

Development settings
~~~~~~~~~~~~~~~~~~~~

To disable the plug-in discovery cache, add the following code to your
``local.settings.php`` file:

.. code-block:: bash

   $settings['cache']['bins']['discovery'] = 'cache.backend.null';

The preceding code will prevent needing to clear caches to register a status
change in a configuration split.


.. _blt-config-split-resources:

Resources
---------

-  `Adding Configuration Split to a Drupal site using BLT and Acquia Cloud
   <https://www.jeffgeerling.com/blog/2017/adding-configuration-split-drupal-site-using-blt-and-acquia-cloud>`__
-  :doc:`BLT multisite documentation </blt/tech-architect/multisite/>`
-  `Configuration split <https://www.drupal.org/project/config_split>`__
-  `Configuration ignore <https://www.drupal.org/project/config_ignore>`__

.. Next review date 20200419
