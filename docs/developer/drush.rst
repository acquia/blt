.. include:: ../../common/global.rst

Drush configuration and aliases
===============================

The ``drush`` directory is intended to contain Drush configuration that is not
website or environment specific.

Website-specific Drush configurations exist in the ``drush/sites/[site-name]``
directory.


.. _blt-drush-website-aliases:

Website aliases
---------------

Remote environments
~~~~~~~~~~~~~~~~~~~~

It's recommended to install Drush aliases in a repository that all developers
can use to access your remote websites (for example,
``drush @mysite.dev uli``).


Acquia Cloud aliases
~~~~~~~~~~~~~~~~~~~~

You can download aliases for Acquia Cloud sites by signing in to
https://cloud.acquia.com, and then going to the **Credentials** tab on your
user profile. Download and place the relevant alias file into ``drush/sites``.

You can also generate aliases using ``blt recipes:aliases:init:acquia`` to
generate your aliases and place them in the ``drush/sites directory``.

.. important::

   The ``blt recipes:aliases:init:acquia`` command is a destructive operation
   and will wipe all existing aliases in the file named ``.yml``. You should
   carefully review the output of this recipe prior to committing (to ensure
   that local aliases or other manual customizations are not lost).

.. Next review date 20200422
