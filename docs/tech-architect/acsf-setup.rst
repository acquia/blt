.. include:: ../../common/global.rst

Configuring a project for use with Acquia Cloud Site Factory
============================================================

To configure a project to run on Acquia Cloud Site Factory, complete the
following steps after initially configuring Acquia BLT, but *before* creating
any websites in ACSF:

#. From the project root, run the following command and commit any changes:

   .. code-block:: bash

      blt recipes:acsf:init:all

#. Optionally, create one or more custom profiles, based on the following
   cases:

   -  If you are using Acquia Lightning, create a custom sub-profile as
      described in :doc:`/lightning/subprofile/`.
   -  For non-Acquia Lightning use cases, you can generate a profile using the
      `Drupal Console
      <https://hechoendrupal.gitbooks.io/drupal-console/content/en/commands/generate-profile.html>`__.

#. Ensure the ``acsf`` module will be enabled when you install websites on
   Acquia Cloud Site Factory, either by adding it as a dependency to your
   custom profile or by adding it to your remote environment configuration
   splits.

#. Deploy to Acquia Cloud using ``blt artifact:deploy``. You can also
   deploy code by using a :doc:`continuous integration configuration
   </blt/tech-architect/deploy/>`.

#. Use the Acquia Cloud Site Factory ``update code`` feature to deploy the
   artifact.

#. When creating a new website, select your custom profile as the profile.

In all other respects, Acquia BLT treats Acquia Cloud Site Factory
installations as multisite installations. To finish setup, including to set
up a local development environment for your Acquia Cloud Site Factory
project, see :doc:`/blt/tech-architect/multisite/`.

.. note::

   When BLT runs Drush commands against multisite installations, it passes both
   a ``uri`` parameter and Drush alias, both of which can be defined per-site
   through ``blt.yml`` files. It's easier to set the local Drush alias
   to ``self`` for all websites and allow BLT to use the ``uri`` exclusively.

.. _blt-acsf-setup-troubleshooting:

Troubleshooting
---------------

If you receive an error such as ``Could not retrieve the site standard_domain
from the database`` when updating code on Acquia Cloud Site Factory, it
indicates one or more websites on your subscription don't have the ``acsf``
connector module enabled and configured. You must enable this module
and then try updating code again.

.. _blt-resources:

Resources
---------

-  :doc:`Acquia Cloud Site Factory documentation </site-factory/>`
-  `Acquia Cloud Site Factory Connector
   <https://www.drupal.org/project/acsf>`__ module on Drupal.org

.. Next review date 20200417
