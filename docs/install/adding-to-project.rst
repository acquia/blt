.. include:: ../../common/global.rst

Adding Acquia BLT to an existing project
========================================

.. important::

   You can add Acquia BLT versions 12 and later to third-party Drupal
   distributions and existing applications. If you are using BLT 11, you must
   instead create a new project from scratch using the BLT project template:
   :doc:`/blt/install/creating-new-project/`


.. _blt-prerequisites:

Prerequisites
-------------

-  The Drupal root must be in a top-level ``docroot`` directory.
-  You must already use `Composer <https://getcomposer.org/>`__ to manage
   website dependencies. If you don't, see `Using Composer to manage
   Drupal website dependencies
   <https://www.drupal.org/docs/develop/using-composer/using-composer-to-manage-drupal-site-dependencies>`__
   and change your project accordingly.
-  Be sure your dependencies are up-to-date by executing the command:
   ``composer update``, and the dependencies don't break your project.


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
      composer require acquia/blt:^12.0 --no-update
      composer update

#. Acquia BLT will place new files in your project directory. Review all
   new and modified files and commit them to Git.

#. Run ``blt doctor`` to diagnose any potential remaining issues requiring
   manual intervention.

#. For information about common instructions and how to begin using Acquia BLT,
   see :doc:`/blt/install/next-steps/`.
