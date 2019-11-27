.. include:: ../../common/global.rst

Adding Acquia BLT to an existing project
========================================

Adding Acquia BLT to an existing project is more complex than creating a new
website with Acquia BLT for the following reasons:

-  Acquia BLT expects your project to have a particular directory structure
   and suite of files.
-  Your existing dependencies may conflict with Acquia BLT's dependencies.


.. _blt-prerequisites:

Prerequisites
-------------

-  The Drupal root must be in a top-level ``docroot`` directory.
-  You must already use `Composer <https://getcomposer.org/>`__ to manage
   website dependencies. If you don't, see `Using Composer to manage
   Drupal website dependencies
   <https://www.drupal.org/docs/develop/using-composer/using-composer-to-manage-drupal-site-dependencies>`__
   and change your project accordingly.
-  Ensure your dependencies are up-to-date using ``composer update``,
   and the dependencies do not break your project.


.. _blt-add-to-existing-project:

Procedure
---------

To add Acquia BLT to a pre-existing Drupal project, complete the following
steps:

#. At the command prompt, change to your existing project directory with the
   ``cd`` command.

#. Use the following commands to configure Composer's ``minimum-stability``
   and ``prefer-stable`` values:

   .. code-block:: bash

      composer config minimum-stability dev
      composer config prefer-stable true

#. Using Composer, install Acquia BLT:

   .. code-block:: bash

      rm -rf vendor composer.lock
      composer require acquia/blt:^9.0.0 --no-update
      composer update

#. Continue with the installation instructions immediately following the
   ``composer create-project``-based step in
   :doc:`/blt/install/creating-new-project/`.

.. Next review date 20200417
