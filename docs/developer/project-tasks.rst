.. include:: ../../common/global.rst

Project tasks
=============

This documentation page describes several common tasks that you will require
as you use Acquia BLT on your local computer.


.. _blt-installing-reinstalling-drupal:

Installing or reinstalling Drupal
---------------------------------

Pre-requisites to installation:

-  Ensure that the ``docroot/sites/default/settings/local.settings.php``
   file exists by running the following command:

   .. code-block:: bash

        blt setup:settings

-  Verify that correct local database credentials are set in the
   ``local.settings.php`` file.
-  Ensure project dependencies have already been built running the
   following command:

   .. code-block:: bash

        blt source:build

To re-install Drupal, run the following command:

.. code-block:: bash

     blt drupal:install

.. note::

   Running the ``blt drupal:install`` command drops the existing database
   tables, and then installs Drupal from scratch.


.. _blt-adding-updating-patching-dependency:

Adding, updating, or patching a dependency
------------------------------------------

For information about managing core and contributed packages for your project,
see :doc:`/blt/developer/dependency-management/`.


.. _blt-deploying-to-ac:

Deploying to Acquia Cloud
-------------------------

For a detailed description of how to deploy to Acquia Cloud, see
:doc:`/blt/tech-architect/deploy/`.


.. _blt-running-tests-code-validation:

Running tests and code validation
---------------------------------

For information about running tests, see :doc:`/blt/developer/testing/`.

To evaluate the project codebase with PHP_CodeSniffer and PHP lint, run the
following command:

.. code-block:: bash

   blt validate:all


.. _building-front-end-assets:

Building front-end assets
-------------------------

For information about compiling front-end assets and running front and
build processes, see :doc:`/blt/developer/frontend/`.


.. _blt-updating-local-environment:

Updating your local environment
-------------------------------

The project is configured to update the local environment with a both local
Drush alias and a remote alias (as defined in ``blt/blt.yml`` or
``blt/local.yml``). Due to the fact that these aliases match those in
``drush/sites/``, you can update the website with Acquia BLT.

For details about how to create these aliases, see
:doc:`/blt/developer/drush/`.

Refresh: Rebuild the codebase, copy the database, and run updates
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The following command will ensure your local computer is synchronized with
the remote environment:

.. code-block:: bash

   blt drupal:sync

This command will sync your website and run all necessary updates, including
clearing the cache, database updates, and configuration imports.

.. tabs::

   .. tab:: 10.x

      By default, Acquia BLT will not sync your public and private files
      directories. However, to perform a file sync during ``sync:refresh``
      tasks in your project, you can set ``sync.files`` to ``true`` in your
      ``blt.yml`` file.

   .. tab:: 11.x

      By default, Acquia BLT will not sync your public and private files
      directories. However, to perform a file sync during ``sync:refresh``
      tasks in your project, you can set ``sync.public-files`` and
      ``sync.private-files`` to ``true`` in your ``blt.yml`` file.

Multisite
~~~~~~~~~

If you are using multisite, you can refresh every multisite on your local
computer by running the following command:

.. code-block:: bash

   blt drupal:sync:all-sites

Sync: Copy the database from the remote site
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: bash

   blt drupal:sync:db

The sync command copies a database (and files if ``sync.files`` is set to
``true``) but does not run any updates afterward.

.. _blt-task-update-local:

Update: Run update tasks locally
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: bash

   blt drupal:update

The previous command runs several update commands (including cache clears,
database updates, and configuration imports) to sync the local database with
your codebase (for example, an exported configuration).

.. Next review date 20200422
