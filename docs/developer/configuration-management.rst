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
projects, configuration split strikes the balance of flexibility,
reliability, and ease of maintenance and development.


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

Configuration stored on disk is essentially a flat-file database and must be
treated as such. For instance, you must make all changes to configuration
through the user interface, through an appropriate API, and then export to disk.
You must never make changes to individual configuration files by hand, in the
same way you must never write a raw SQL query to add a Drupal content type. Even
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

.. Next review date 20200422
