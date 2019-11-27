.. include:: ../../common/global.rst

Creating a new project with Acquia BLT
======================================

.. important::

   To use Acquia BLT, do not clone either Acquia BLT or the ``blt-project``
   project.

To create a new Acquia BLT project, complete the following steps:

#. Determine a *machine name* for your new project (such as ``my-project``).
   For compatibility with third-party tools, Acquia recommends you use
   only letters, numbers, and hyphens for the machine name.

#. In a command prompt window, run the following command to both create your
   new project and download all dependencies (including Acquia BLT):

   .. code-block:: bash

      composer create-project --no-interaction acquia/blt-project my-project

#. Change to your project directory. For example:

   .. code-block:: bash

      cd my-project

#. If this is your first time using Acquia BLT on this computer, restart your
   shell to allow Bash to detect the new Acquia BLT alias.

#. Customize your ``blt/blt.yml`` file to select an install profile. The
   `build.yml <https://github.com/acquia/blt/blob/10.x/config/build.yml>`__
   file includes all available configuration values.

   By default, Acquia BLT installs websites using the :doc:`Lightning
   </lightning/>` profile. You can change this setting to any other core,
   contributed, or custom profile in your codebase.
   
   To change to another profile, complete the following steps:
   
   a. Download the profile of your choice, based on the following command:

      .. code-block:: bash

              composer require acquia/headless_lightning:~1.1.0

   #. Enter the name of the profile in the ``blt/blt.yml`` file, in the
      ``profile:name`` setting. For example:

      .. code-block:: text

              profile: name: minimal

#. Start your application hosting stack (also called a `LAMP
   <https://en.wikipedia.org/wiki/LAMP_(software_bundle)>`__ stack) by
   running the following command to create a Drupal VM instance:

   .. code-block:: bash

        blt vm

   To customize your virtual machine (VM), such as to enable Solr or to
   change the PHP version, respond *no* when Acquia BLT offers to boot your
   VM, and then `make any necessary modifications
   <http://docs.drupalvm.com/en/latest/getting-started/configure-drupalvm/>`__
   to the ``box/config.yml`` file before starting your VM.

   .. admonition:: Using your own LAMP stack

        To set up your own LAMP stack, see
        :doc:`/blt/install/local-development/`, and then execute the following
        command to generate default local settings files:

        .. code-block:: text

           blt blt:init:settings

        Change the generated
        ``docroot/sites/default/settings/local.settings.php`` file by adding
        your custom MySql credentials.

#. Install Drupal and generate any remaining required files (such as
   ``settings.php`` or hash salt) by running the following command:

   .. code-block:: bash

        blt setup

#. Sign in to Drupal by running the following command:

   .. code-block:: bash

        drush @my-project.local uli

   Be sure to replace ``my-project`` with the name of your project, which must
   be the value of ``project.machine_name`` in the ``blt/blt.yml`` file.

You now have a running local Drupal website that uses Acquia BLT.

For information about common instructions and how to begin using Acquia BLT,
see :doc:`/blt/install/next-steps/`.


.. _blt-troubleshooting:

Troubleshooting
---------------

If you have trouble creating the project, attempt to resolve the issue by
using one of the following methods:

*  Clear the `Composer <https://getcomposer.org/>`__ cache.
*  Increase the process timeout by running the following command:

   .. code-block:: bash

        composer clear-cache
        export COMPOSER_PROCESS_TIMEOUT=2000

If you have trouble using the ``blt`` alias, verify the alias is installed
as expected, and then restart your terminal session by running the following
commands, based on your version of Acquia BLT:

.. tabs::

   .. tab:: 10.x

      .. code-block:: bash

         ./vendor/bin/blt blt:init:shell-alias
         source ~/.bash_profile

   .. tab:: 9.2.x

      .. code-block:: text

         composer run-script blt-alias
         source ~/.bash_profile

If you receive syntax errors from vendor packages, ensure that the version of
PHP on your host matches the version of PHP in your VM, or else be sure to
always run Composer commands from the VM.

Additional troubleshooting resources
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you continue to have issues with Acquia BLT, review the following
resources:

-  :doc:`Review other Acquia BLT documentation pages </blt/>`.
-  `Search for relevant issues <https://github.com/acquia/blt/issues>`__.
-  `Create a new issue <https://github.com/acquia/blt/issues/new>`__.

.. Next review date 20200424
