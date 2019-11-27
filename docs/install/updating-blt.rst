.. include:: ../../common/global.rst

Updating Acquia BLT
===================

Select from the following update options, depending on whether or not you are
using Composer.


.. _blt-updating-composer-managed-version:

Updating a Composer-managed version
-----------------------------------

If you are already using Acquia BLT with Composer, you can update to the
latest version of Acquia BLT using Composer.

#.  Run the following command:

    .. code-block:: bash

       composer update acquia/blt --with-all-dependencies

    This will cause Composer to update all of your dependencies (in accordance
    with your version constraints) and permit the latest version of Acquia BLT
    to be installed.

#.  Examine the `release information
    <https://github.com/acquia/blt/releases>`__ to determine if there are
    special update instructions for the new version.

#.  Review and commit changes to your project files.

#.  Occasionally, you may need to refresh your local environment by running
    the following command:

    .. code-block:: bash

         blt setup

    This will drop your local database and re-install Drupal.


.. Next review date 20200422
