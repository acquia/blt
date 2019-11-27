.. include:: ../../common/global.rst

Next steps with Acquia BLT
==========================

After your new project works locally, review the :doc:`Acquia BLT
documentation </blt/>` by role to learn how to perform common project tasks
and integrate with third party tools.

Here are tasks that are typically performed at this stage:

-  :doc:`Initialize continuous integration (CI)
   </blt/tech-architect/ci/>`. You can use one of the following commands,
   based on your CI tooling:

   * .. code-block:: bash

        blt recipes:ci:pipelines:init

   * .. code-block:: bash

        blt recipes:ci:travis:init

-  Push to your upstream repository using the following Git commands:

   .. code-block:: bash

      git add -A
      git commit -m 'My new project is great.'
      git remote add origin [something]
      git push origin

-  Ensure that you have entered a value for ``git.remotes`` in the
   ``blt/blt.yml`` file:

   .. code-block:: text

      git:
        remotes:
            - bolt8@svn-5223.devcloud.hosting.acquia.com:bolt8.git

-  :doc:`Create and deploy an artifact </blt/tech-architect/deploy/>`.

   .. code-block:: bash

      blt artifact:deploy

Acquia BLT includes additional commonly used commands, as follows:

.. list-table::
   :widths: 50 50
   :header-rows: 1
   :class: verticaltable

   * - Command
     - Description
   * - ``blt``
     - List targets.
   * - ``blt validate``
     - Validate code using commands that include ``phpcs``, ``php lint``, and
       ``composer validate``.
   * - ``blt tests:phpunit:run``
     - Run phpunit tests.
   * - ``blt tests:behat:run``
     - SSH into a virtual machine and run Behat tests.
   * - ``blt doctor``
     - Diagnose issues with Acquia BLT.
   * - ``composer require drupal/ctools:^8.3.0``
     - Download and require a new project.
   * - ``blt artifact:build``
     - Build a deployment artifact.
   * - ``blt artifact:deploy``
     - Build an artifact and deploy it to ``git.remotes``.
   * - ``composer update acquia/blt --with-dependencies``
     - Update Acquia BLT.


.. _blt-drush-aliases:

Drush aliases
-------------

For more information about Drush aliases, see :doc:`/blt/developer/drush/`.


.. _blt-adding-settings-settings-php:

Adding settings to settings.php
-------------------------------

A common practice in Drupal is to add settings to the ``settings.php`` file to
control things like cache backends, set site variables, or other tasks which
do need a specific module. Acquia BLT provides two mechanisms to add settings
to your ``settings.php`` file:

*  Settings files can be added to the ``docroot/sites`` directory for
   inclusion in all websites in the codebase.
*  Settings can be added using an ``includes.settings.php`` in the
   ``settings`` directory of an individual website (such as
   ``docroot/sites/{site-name}/settings/includes.settings.php``).

Both mechanisms allow settings to be overridden by ``local.settings.php``, to
support local development.

.. important::

   When using Acquia BLT, do not add settings directly to ``settings.php``.
   This is also the case with Acquia Cloud Site Factory, which will ignore
   settings directly added to ``settings.php``.

The first level of Acquia BLT's settings management is the
``blt.settings.php`` file. When websites are created, Acquia BLT adds a
require line to the standard ``settings.php`` file, which includes the
``blt.settings.php`` file from Acquia BLT's location in the vendor directory.
This file then controls the inclusion of other settings files in a hierarchy.
The full hierarchy of settings files used by Acquia BLT appears similar to
the following:

.. code-block:: text

   sites/{site-name}/settings.php
    |
    ---- blt.settings.php
           |
           ---- sites/settings/*.settings.php
           |
           ---- sites/{site-name}/settings/includes.settings.php
           |       |
           |       ---- foo.settings.php
           |       ---- bar.settings.php
           |       ---- ....
           |
           ---- sites/{site-name}/settings/local.settings.php

.. important::

   Do not edit the ``blt.settings.php`` file in the vendor directory. If you
   do, the next time ``composer update`` or ``install`` runs, your changes
   may be lost. Instead, use one of the following mechanisms:


   -  **Global settings for the codebase**

      To allow settings to be made once and applied to all sites in a
      codebase, Acquia BLT globs the ``docroot/sites/settings`` directory to
      find all files matching a ``*.settings.php`` format, and then adds them
      using PHP ``require`` statements.

      As not all projects will need additional global settings, Acquia BLT
      initially deploys a ``default.global.settings.php`` file into the
      ``docroot/sites/settings`` directory. To make use of this file, rename
      the file to ``global.settings.php``, and then add settings or required
      files as needed.

   -  **Per website**

      On a per-website basis, Acquia BLT uses an ``includes.settings.php``
      file in the ``settings`` directory of each individual website. Any
      settings made in that file, or other files required into it, will be
      added to the settings for that particular website only.

      As not all projects require additional includes, Acquia BLT initially
      deploys a ``default.includes.settings.php`` file into the website's
      ``docroot/sites/{site_name}/settings`` directory. To make use of this
      file, rename it to ``includes.settings.php``, and then add the path to
      the file or files to be added.

.. Next review date 20200423
