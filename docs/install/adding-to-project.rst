.. include:: ../../common/global.rst

Adding Acquia BLT to an existing project
========================================

Acquia BLT is best used to create new projects from scratch, but you
can also add Acquia BLT to an existing project to receive all the
:ref:`same benefits <blt-features>`.

Adding Acquia BLT to an existing project is more complex than creating a new
website with Acquia BLT. Acquia BLT includes a project template and
expects the files in your project to match the structure and contents of the
template.

Acquia BLT will try to generate any missing files during installation, but some
projects may require manual reconciliation. Adhering to the following
prerequisites and instructions should ensure the expected outcome.


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
      composer require acquia/blt:^10.0 --no-update
      composer update

#. Replace your ``.gitignore`` file with Acquia BLT's template ``.gitignore``,
   then re-add any project-specific overrides:

   .. code-block:: bash

      cp vendor/acquia/blt/subtree-splits/blt-project/.gitignore .

#. (*Optional*, but recommended) Replace your ``composer.json`` with Acquia
   BLT's template ``composer.json``, and re-add any packages or other
   configuration you want to preserve from your existing ``composer.json``:

   .. code-block:: bash

      cp vendor/acquia/blt/subtree-splits/blt-project/composer.json .

#. Acquia BLT will place new files in your project directory. Review all
   new and modified files and commit them to Git.

#. Run ``blt doctor`` to diagnose any potential remaining issues requiring
   manual intervention.

#. Continue with the installation instructions directly following the
   ``composer create-project``-based step in
   :doc:`/blt/install/creating-new-project/`.

.. Next review date 20201223
