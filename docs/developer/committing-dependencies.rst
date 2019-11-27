.. include:: ../../common/global.rst

Committing dependencies
=======================

`Composer's <https://getcomposer.org/>`__ official stance is you must not
commit dependencies.

On occasion, extenuating circumstances require you to commit your
dependencies. For Acquia BLT, you can commit your dependencies by
using the following steps:

#. Change your project's ``.gitignore`` by removing the following lines:

   .. code-block:: text

      docroot/core
      docroot/modules/contrib
      docroot/themes/contrib
      docroot/profiles/contrib
      docroot/libraries
      drush/contrib
      vendor

#. Create a custom ``deploy.exclude_file`` and reference its location in
   your ``blt.yml`` file:

   .. code-block:: bash

      mkdir blt/deploy && cp vendor/acquia/blt/scripts/blt/deploy/deploy-exclude.txt blt/deploy/deploy-exclude.txt

      deploy:
        exclude_file: ${repo.root}/blt/deploy/deploy_exclude.txt

#. Change your custom ``deploy_exclude.txt`` file by removing the following
   lines:

   .. code-block:: text

      /docroot/core
      /docroot/libraries/contrib
      /docroot/modules/contrib
      /docroot/sites/*/files
      /docroot/sites/*/private
      /docroot/themes/contrib
      /drush/contrib
      /vendor

#. Configure ``deploy.build-dependencies`` to ``false`` in your
   ``blt/blt.yml`` file:

   .. code-block:: text

      deploy:
        build-dependencies: false

#. Commit your changes and dependencies:

   .. code-block:: bash

      git add -A
      git commit -m 'Committing dependencies.'

Your dependencies will now be committed to your repository and copied to
your deployment artifact.

.. Next review date 20200419
