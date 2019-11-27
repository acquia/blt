.. include:: ../../../common/global.rst

Configuring Acquia BLT with Docksal
===================================

By default, Acquia BLT works with Drupal VM. Although it can be easy to set up
Acquia BLT to use with Docksal, you must install the Docksal ``blt`` add-on
command and then make some configuration changes.


.. _blt-configuring-project-docksal:

Configuring your project to use Docksal
---------------------------------------

If you already have Docksal installed on your computer, complete the
following steps to configure your Acquia BLT project to use Docksal:

#.  Add a ``.docksal`` directory to your project.
#.  Run the following command in your project directory:

    .. code-block:: bash

       fin up

#.  For websites hosted on Acquia Cloud, you may want to set the stack to
    Acquia by running the following command to configure the project with
    Varnish®, Memcache, and Solr:

    .. code-block:: bash

       fin config set DOCKSAL_STACK=acquia

For specific information about Docksal, see the `Docksal documentation
<https://docs.docksal.io>`__.


.. _blt-using-docksal-blt-command:

Using the Docksal blt command
-----------------------------

Acquia BLT will install a ``blt alias`` command on your host computer. Running
the ``blt`` command will then use the resources of your host computer.
Instead, you need to ensure you are running the commands with the Docksal CLI
service.

Install the Docksal ``blt`` add-on command by running the following command:

.. code-block:: bash

   fin addon install blt

Run any ``blt`` command with ``fin`` to run the command from the Docksal
command-line interface, similar to the following command:

.. code-block:: bash

   fin blt doctor


.. _blt-config-db-connection-drush-alias:

Configuring the database connection and Drush alias
---------------------------------------------------

New project configuration
~~~~~~~~~~~~~~~~~~~~~~~~~

The default Docksal database settings are different than the Acquia BLT
defaults. If you have not already added special settings to the
``local.settings.php`` file that Acquia BLT creates, it is best to begin by
following these steps:

#. Remove the ``docroot/sites/default/settings/local.settings.php`` and
   the ``docroot/sites/default/local.drush.yml`` files.

#. Update the ``blt.yml`` file in your project's ``blt`` directory with the
   following information:

   .. code-block:: text

      project:
        machine_name: myproject
        local:
          hostname: '${env.VIRTUAL_HOST}'
      drupal:
        db:
          database: default
          username: user
          password: user
          host: db
          port: 3306

#. Initiate and verify ``blt`` settings by running the following command:

   .. code-block:: bash

      fin blt blt:init:settings

#. Configure your Drupal website with the following command:

   .. code-block:: bash

      fin blt setup -D setup.strategy=install

.. note::

   By default, because Acquia BLT defaults to the install strategy, passing
   the strategy option here is not required. You may also use a sync setup
   strategy if there is an existing remote with a source database to use in a
   Drush ``sql:sync operation``:

   .. code-block:: bash

      fin blt setup -D setup.strategy=sync

Existing project configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you have an existing Acquia BLT project with Drupal install, you may
prefer to configure your ``local.settings.php`` file and your
``local.drush.yml`` file. It is best practice to also update your ``blt.yml``
as previously outlined, but it's not necessary to delete these files.

#. Configure your ``docroot/sites/default/settings/local.settings.php``
   file with the following variable values:

   .. code-block:: php

      $db_name = 'default';

      $databases = array(
        'default' =>
        array(
          'default' =>
          array(
            'database' => $db_name,
            'username' => 'user',
            'password' => 'user',
            'host' => 'db',
            'port' => '3306',
            'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
            'driver' => 'mysql',
            'prefix' => '',
          ),
        ),
      );

#. Configure your ``docroot/sites/default/local.drush.yml`` file with the
   following settings:

   .. code-block:: yaml

      options:
        uri: 'http://myproject.docksal'

#. Assuming your website's Drush remote is ``@sitegroup.siteid``, you can
   update your Acquia BLT configuration (either at the project level or at the
   website level). |br|
   At the project level (``blt/blt.yml``) or at the website level (such as
   ``sites/default/blt.yml``), make the following file edits:

   .. code-block:: text

      setup:
        strategy: sync

      drush:
        aliases:
          remote: sitegroup.siteid

When setting up the project for the first time and when running
``fin blt setup``, the command will execute the sync setup strategy rather
than the install strategy (``sql:sync vs site:install``). This effectively
executes the following:

.. code-block:: text

   drush sql:sync @sitegroup.siteid @self

For more information, see this `extended blog post
<https://blog.docksal.io/docksal-and-acquia-blt-1552540a3b9f>`__
with a walkthrough to set up a new Acquia BLT project with Docksal.


.. _blt-settings-docksal-ci-env:

Settings for a Docksal-enabled CI Environment
---------------------------------------------

To continuously verify that a profile installs successfully and existing
sites also synchronize correctly, set two different environment-specific
``blt.yml`` files in the ``default`` site directory (``install.blt.yml`` and
``sync.blt.yml``). Each of these files can contain different Behat
configurations for installing and syncing websites, and then running different
sets of Behat tests on each. You can then have the Docksal command
``fin test-environment``, which accepts an argument meant to be the
environment indicator. The ``fin test-environment`` command can run the
following command:

.. code-block:: text

   fin blt setup --environment=$1 && fin blt tests --environment=$1

This command will set up the website according to the configuration,
and then run tests against that website according to the same set of
configurations.

.. Next review date 20200424
