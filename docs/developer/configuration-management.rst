.. include:: ../../common/global.rst

Configuration management with Acquia BLT
========================================

.. _blt-config-mgmt-overview:

Acquia BLT supports several methods of configuration management (CM)
in Drupal 8. All configuration management methods rely to varying degrees on
Drupal core's configuration entities, which can be imported into a database or
exported to disk as ``yml`` files.

Acquia BLT *strongly recommends* a CM workflow based on the
`Configuration split <https://www.drupal.org/project/config_split>`__ module,
described in `Managing Configuration with Config Split
<https://support.acquia.com/hc/en-us/articles/360024009393>`__. For most
projects, configuration split strikes the best balance of flexibility,
reliability, and ease of maintenance and development. A Features-based
workflow (analogous to most CM workflows in Drupal 7) can better handle
certain multisite architectures, but has a much greater development and
maintenance overhead.


.. _blt-gen-principles-config-mgmt:

General principles
------------------

The following section describes aspects of Acquia BLT's development
and deployment process common to all CM workflows.

Basics of configuration management
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The primary goal of configuration management is to ensure you can review,
test, and predictably deploy all configuration changes to production
environments. Some easy changes, such as changing a website's name or slogan,
can have limited, atomic, and predictable effects, and not require strict
change management. Other types of changes, such as modifying field storage
schemas, *always* must go through a review process. Different projects can
have different degrees of risk tolerance. For instance, some can prefer
configuration be strictly read-only in production, prohibiting even
the easy website name change.

A good CM workflow must be flexible enough to handle either of the preceding
use cases. A CM workflow must make it easy for developers to make, and capture
configuration changes, review, and test the changes, and reliably deploy the
changes to a remote environment.

A configuration change follows the following lifecycle:

#. A developer makes the change in their local environment.
#. The developer uses CM commands to export the configuration change to
   disk.
#. The developer commits the new or updated configuration to VCS and
   opens a pull request.
#. Automated testing ensures the configuration can be installed
   from scratch on a new website and imported without conflicts on
   an existing website.
#. After you deploy the change, deployment hooks automate the import of the
   new or updated configuration.

Configuration is captured and deployed between environments
in Drupal 8 typically through YAML files. The YAML files, stored in a root
``config`` directory, or distributed with individual modules in
``config/install`` directories, represent individual configuration objects
synchronized with the active configuration in an environment's database by
several methods. For more information, see the `documentation about core
configuration management
<https://www.drupal.org/docs/8/configuration-management>`__ on Drupal.org.

The following documentation addresses the challenge of capturing (exporting),
and deploying (importing) configuration in a consistent way to support the
workflow already described.

How Acquia BLT handles configuration updates
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Acquia BLT-based projects already support the workflow, including
automated imports of configuration updates. Acquia BLT defines a
generic ``drupal:update`` task applying any pending database and configuration
updates. The same task can be re-used locally or remotely (through the
``drupal:update``, and ``artifact:update:drupal`` wrappers, respectively) to
ensure configuration changes and database updates behave identically in all
environments.

When you run one of the update commands, they perform the following
updates (see ``drupal:config:import``):

-  Database updates: the same as running ``drush updb`` or hitting
   ``update.php``, applying any pending database updates.
-  Configuration import: runs the core configuration-import command to import
   any configuration stored in the root ``config`` directory. The import is
   either a full or partial import, depending on how you configure
   Acquia BLT.
-  Features import (optional): runs ``features-import-all``, which imports
   any configuration stored in a feature module's ``config/install``
   directory.

   .. note::

      The ``features-import-all`` command only runs if you have configured the
      ``cm.features.bundle`` property in ``blt/blt.yml``.

There are also pre and post-config import hooks you can use to run custom
commands.

Configuration versus content
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can't use Drupal's configuration system to manage entities Drupal
considers to be content, such as nodes, taxonomy terms, and files. This
can create conflicts when a configuration entity depends on a content
entity, such as:

-  You have a block type including a file upload field, and you want
   to place the block in a theme and export the block configuration.
-  You have a view filtered by a static taxonomy term, and you want to
   export the view configuration.

In such cases, the exported configuration file for the block or view
will define a dependency on a content object (referenced by UUID). If
the content doesn't exist when you import the configuration, the
import will fail.

The solution is to ensure the referenced content exists before you import the
configuration. Two recommended methods include:

-  Use the `Default Content
   <https://www.drupal.org/project/default_content>`__ module to export the
   referenced content as JSON files, and store the files with a feature or
   other dedicated module.
-  Use Migrate and a custom module to create default content from any
   number of custom sources, such as JSON files stored with your feature.

Updating core and contributed modules
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Use caution updating core and contributed modules when using any
sort of configuration management process. If those updates make changes
to a module's configuration or schema, you must ensure you update your
exported configurations. Otherwise, the next time you import the configuration
(such as on deploys), the import of your configuration will overwrite changes
made by the database updates. Your database and codebase will become out of
sync. Failing to take the preceding scenario into account is the most common
cause of configuration imports failing Acquia BLT's test for overridden
configuration.

The best way to prevent such problems is to always use the following steps
when updating contributed and core modules. The steps assume you are
using configuration split or a similar CM strategy using ``drush cex`` and
``drush cim``:

#. Start from a clean install or database sync, including config import
   (``blt setup`` or ``blt drupal:sync``). Ensure that your active and
   exported configuration are in sync (running ``drush config-export``
   must report no changes).
#. Use ``composer update`` or
   ``composer update drupal/[module_name] --with-dependencies`` to
   download the new module version(s). To update Drupal core, use the
   following command:

   .. code-block:: bash

       composer update webflo/drupal-core-require-dev drupal/core --with-dependencies

#. Run the following command to apply any pending updates locally:

   .. code-block:: bash

       drush updb

#. Export any changed configuration as part of the database updates
   or new module versions by running ``drush config-export`` and determining
   if there are any changes on disk by running ``git status``.
#. Commit any changed configuration, along with the updated
   ``composer.json`` and ``composer.lock`` files.

Acquia is working on a better way of preventing the codebase and database from
becoming out of sync other than manually monitoring module updates. Find more
information on these issues at `Features and contributed module updates
<https://www.drupal.org/node/2745685>`__, and `Testing for schema changes to
stored configuration <https://github.com/acquia/blt/issues/842>`__.

Ensuring the integrity of stored configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Configuration stored on disk, whether through the core configuration system
or features, is essentially a flat-file database and must be treated as
such. For instance, you must make all changes to configuration through the
user interface, through an appropriate API, and then export to disk. You
must never make changes to individual configuration files by hand, in the same
way you must never write a raw SQL query to add a Drupal content type. Even
seemingly small changes to one part of the configuration can have sweeping and
unanticipated changes. For instance, enabling the `Panelizer
<https://www.drupal.org/project/panelizer>`__ or `Workbench
<https://www.drupal.org/project/workbench>`__ modules will change the
configuration of every content type on the website.

Acquia BLT has a built-in test helping to protect against some of these
mistakes. After you import the configuration such as during ``drupal:update``
or ``artifact:update:drupal``, the tests will check if any configuration
remains overridden. If so, the build will fail, alerting you to uncaptured
configuration changes or a corrupt configuration export. The test assists in
monitoring and must not be disabled, but if you must temporarily disable it in
an emergency such as if deploys to a cloud environment are failing, you can do
so by settings ``cm.allow-overrides`` to ``true``.

You must enable protected branches in GitHub to ensure you can only
merge pull requests if they are up to date with the target branch. Enabling
protected branches guards against where one pull request adds a new content
type, while another pull request enables Workbench changing the content type.
Individually, each pull request is valid, but once they are both merged, they
produce a corrupt configuration where the new content type lacks Workbench
configuration. When used with Acquia BLT's built-in test for configuration
overrides, protected branches can quite effectively prevent some forms of
configuration corruption.

For an ongoing discussion on ensuring configuration integrity, see
https://www.drupal.org/node/2869910.


.. _blt-config-split-workflow:

Configuration split workflow
----------------------------

For detailed information on how you can create and enable configuration
splits, see :doc:`/blt/developer/config-split/` and `Managing Configuration
with Config Split
<https://support.acquia.com/hc/en-us/articles/360024009393>`__.

If Acquia is not working with the configuration split module, ensure you are
using Drush version 8.1.10 or later, Configuration Split version
8.1.0-beta4 or later, and that you configured ``cm.strategy`` to
``config-split`` in the ``blt/blt.yml`` file.


.. _blt-using-update-hooks:

Using update hooks to import individual config files
----------------------------------------------------

Acquia BLT runs module update hooks before importing configuration changes.
If you must import a configuration change before the update hook runs, in
your hook, you must import the needed configuration from files first. An
example would be adding a new taxonomy vocabulary through configuration, and
populating the vocabulary with terms in an update hook.

The following code snippet demonstrates importing a taxonomy vocabulary
configuration first before creating terms in the vocabulary:

.. code-block:: text

   use Drupal\taxonomy\Entity\Term;

   // Import taxonomy from config sync directory.
   $vid = 'foo_terms'; // foo_terms is the vocabularly id.
   $vocab_config_id = "taxonomy.vocabulary.$vid";
   $vocab_config_data = foo_read_config_from_sync($vocab_config_id);
   \Drupal::service('config.storage')->write($vocab_config_id, $vocab_config_data);

   Term::create([
     'name' => 'Foo Term 1',
     'vid' => $vid',
   ])->save();

   Term::create([
     'name' => 'Foo Term 2',
     'vid' => $vid',
   ])->save();

The preceding code depends on a helper function, which you can add to your
custom profile:

.. code-block:: text

   use Drupal\Core\Config\FileStorage;

   /**
    * Reads a stored config file from config sync directory.
    *
    * @param string $id
    *   The config ID.
    *
    * @return array
    *   The config data.
    */
   function foo_read_config_from_sync($id) {
     // Statically cache FileStorage object.
     static $storage;

     if (empty($storage)) {
       global $config_directories;
       $storage = new FileStorage($config_directories[CONFIG_SYNC_DIRECTORY]);
     }
     return $storage->read($id);
   }


.. _blt-features-based-workflow:

Features-based workflow
-----------------------

The `Features <https://www.drupal.org/project/features>`__ module allows
you to bundle related configuration files such as a content type and its
fields into individual feature modules. Drupal treats features like normal
modules, but Features and its dependencies allow features to provide default
configuration and update changes to the configuration.

Due to the modular architecture, Features is a better solution for certain
multisite applications where you must customize features on a per-website
basis. If you have several content types exported as separate features,
but a website needs a subset of those content types, you can disable the
unused features for a cleaner content editing experience. This also has the
advantage of logically grouping features and custom code alongside its
corresponding configuration.

The downside to the more granular approach is Features can't make some of the
same assumptions as the core configuration system. Features relies much more
heavily on the developer to manage the architecture and handle configuration
changes Features can't handle. Relying on the developer to handle
configuration changes makes the system much more error-prone and more of a
burden to maintain.

To configure a Features-based workflow, you must configure ``cm.strategy``
to ``features`` in the ``blt/blt.yml`` file.

Using bundles
~~~~~~~~~~~~~

Features allows you to define custom "bundles" essentially letting you train
Features to support your project's individual workflow. Bundles are a way to
namespace your features. You want to choose a bundle name based on your
project name (an Acme bundle would prefix all your feature server names with
``acme\_``).

Bundles can also do more to make your life easier. For instance,
Features automates suggested features based around content types and
taxonomies. If you want to create features for custom block types, for
example, you can configure your preference in the custom bundle. You can
choose to exclude certain types of configuration. You can exclude permissions,
or group certain types of configuration such as field storage into a core
bundle, which is helpful for breaking circular dependencies.

.. note::

   As of version 8.3.3, Features can manage user roles and permissions, but
   not independently. You can only export permissions for an entire role at
   once, unlike in Drupal 7, where you can export roles and their associated
   permissions separately.

For this reason, Features excludes roles and permissions by default. If you
want to export roles and permissions, change the ``alters`` configuration on
your Features bundle, see `User permission handling
<https://www.drupal.org/node/2383439>`__.

Testing features
~~~~~~~~~~~~~~~~

You must ensure, through automated testing, your features can be
installed on a new website and enabled on existing sites.

Features can fail to install or import properly for various reasons. The most
frequent cause is circular dependencies. For instance, if feature A depends on
a field exported in feature B, and feature B depends on a field exported in
feature A. You can't enable either feature first, and website installs will
break. Circular dependencies are not important if you have a single-website
installation, but you want to prevent them if you are building a multisite
platform.

A feature can also stay overridden after importing, due to another module
overriding the provided configuration. For instance, the Workbench module
adds a special field to content types when enabled. If the field is not
exported to the feature containing a content type, the feature will be
perpetually overridden. Overriding the features is not necessarily harmful,
but can make it difficult to diagnose other more serious issues. Acquia
recommends configuring Acquia BLT's CM ``allow overrides`` property to
``false`` to automate testing for overrides.

You can use the following code snippet in your profile's install file to
enable all features in a given bundle:

.. code-block:: text

   <?php
   $available_modules = system_rebuild_module_data();
   $dependencies = array();
   foreach ($available_modules as $name => $module) {
     if ($module->info['package'] == 'My Bundle') {
       $dependencies[] = $name;
     }
     \Drupal::service('module_installer')->install($dependencies);
   }


Updating custom fields and schema
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Features and the core configuration system don't handle some configuration
changes well, including:

-  Updating field storage such as changing a single-value field to an
   unlimited-value field
-  Adding a `new custom block
   type <https://www.drupal.org/node/2702659>`__ to an existing feature. You
   must create a new feature for every block type
-  Deleting a field (you must remove the field from the feature
   and then use the following code snippet to delete the field)
-  Adding a field to some types of content (such as `block content
   <https://www.drupal.org/node/2661806>`__)
-  Adding several configuration entities at once depending on one another
   (leading to `cryptic exceptions <https://www.drupal.org/node/2726839>`__
   when you run ``features-import``, use the following workaround)

To handle the configuration changes, you want to use update hooks. For
example, you can use the following snippet of code to create or delete a
field:

.. code-block:: text

   use Drupal\field\Entity\FieldStorageConfig;
   use Drupal\field\Entity\FieldConfig;

   // Create a new field.
   module_load_include('profile', 'foo', 'foo'); // See below; foo is your profile name.
   $storage_values = foo_read_config('field.storage.block_content.field_my_new_field', 'foo_feature');
   FieldStorageConfig::create($storage_values)->save();
   $field_values = foo_read_config('field.field.block_content.foo_my_block.field_my_new_field', 'foo_feature');
   FieldConfig::create($field_values)->save();

   // Delete an existing field.
   $field = FieldStorageConfig::loadByName('block_content', 'field_my_field');
   $field->delete();

The preceding code depends on a helper feature such as the following code,
which Acquia suggests adding to your custom profile. Lightning includes the
helper feature out-of-the-box:

.. code-block:: text

   use Drupal\Core\Config\FileStorage;
   use Drupal\Core\Config\InstallStorage;

   /**
    * Reads a stored config file from a module's config/install directory.
    *
    * @param string $id
    *   The config ID.
    * @param string $module
    *   (optional) The module to search. Defaults to 'foo' profile (not technically
    *   a module, but profiles are treated like modules by the install system).
    *
    * @return array
    *   The config data.
    */
   function foo_read_config($id, $module = 'foo') {
     // Statically cache all FileStorage objects, keyed by module.
     static $storage = [];

     if (empty($storage[$module])) {
       $dir = \Drupal::service('module_handler')->getModule($module)->getPath();
       $storage[$module] = new FileStorage($dir . '/' . InstallStorage::CONFIG_INSTALL_DIRECTORY);
     }
     return $storage[$module]->read($id);
   }

Overriding configuration
~~~~~~~~~~~~~~~~~~~~~~~~

Drupal typically prevents modules from overriding configuration that already
exists in the system, producing an exception such as the following:

.. code-block:: text

   Configuration objects (foo) provided by bar already exist in active configuration

If you must override the default configuration provided by another
project (or core), the available solutions include:

-  Recommended: use Features. Features will prevent a
   ``PreExistingConfigException`` from being thrown when a feature
   containing pre-existing configuration is installed. Ensure Features is
   already enabled before installing any individual features containing
   configuration overrides (listing Features as a dependency is not enough).
-  Move your configuration into a custom profile. Configuration imports for
   profiles are treated differently than for modules. Importing pre-existing
   configuration for a profile won't throw a ``PreExistingConfigException``.
-  Use `config rewrite <https://www.drupal.org/project/config_rewrite>`__,
   which will allow you to rewrite the configuration of another module prior
   to installation.
-  Use the `config override system
   <https://www.drupal.org/docs/8/api/configuration-api/configuration-override-system>`__
   built into core. For awareness, the configuration override system has `some
   limitations <https://www.drupal.org/node/2614480#comment-10573274>`__.

Other caveats
~~~~~~~~~~~~~

Be aware that reverting all features and configurations on every deploy
creates a risk of discarding server-side changes. You must control the risk by
managing permissions with caution. You must balance the risk against the
greater risk of allowing for divergent configuration between your database and
VCS.

Configuration Management in Drupal 8 is still improving early in the Drupal 8
lifecycle. You must continue to watch Drupal Core's issue queue and `Drupal
Planet <https://www.drupal.org/planet>`__ blog posts for refinements to the CM
workflows explained here.

Features is a ground-up rewrite in Drupal 8 and is maturing fast, but may
still have some traps. Developers must keep a close eye on exported features,
and architects must review features in pull requests for the preceding caveats
and best practices.

.. Next review date 20200422
