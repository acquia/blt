.. include:: ../../common/global.rst

Onboarding with Acquia BLT
==========================

This document is intended for developers who have joined a project that
already has Acquia BLT installed. It is a quick-start guide for getting your
local development environment set up and getting oriented with the project
standards and workflows.

If you are attempting to add Acquia BLT to a project or create a new Acquia
BLT-based project, do not use this document. Instead, refer to
:doc:`/blt/install/`.


.. _blt-onboarding-before-you-start:

Before you start
----------------

If you have been directed to this documentation by a project that's using
Acquia BLT to accelerate its development, testing, and deployment, we
recommend exploring all of the Acquia BLT documentation first. Here's
what's most important to do and know before getting started:

-  Acquia BLT is distributed as a Composer package. This means that the
   project you are working on requires Acquia BLT as a dependency in its
   ``composer.json`` file. This also means that you don't need to install or
   configure Acquia BLT globally on your computer, or as a separate tool—run
   ``composer install`` on the parent project and install a bash alias (as
   described below), and you're ready to go.
-  You will need some project-specific information to set up your local
   environment, specifically whether you are using a virtual development
   environment (for example, DrupalVM), and the name of your mainline
   development branch (``develop`` or ``master``). This should be referenced
   in your project's README file.
-  If you need help, talk to your project team first, since they may have
   already encountered any issue you are experiencing. Then post an issue in
   the `Acquia BLT issue queue <https://github.com/acquia/blt/issues>`__. The
   issue queue isn't only for bugs—we welcome feedback on all aspects of the
   developer experience.
-  Verify that your local system and network meet the
   :doc:`Acquia BLT system requirements </blt/install/>`. Also ensure that you
   have dependencies installed for any virtual development environment (such
   as
   :ref:`VirtualBox and Vagrant for DrupalVM <blt-drupal-vm-blt-projects>`).
-  Since Acquia BLT makes use of a variety of best practice development
   tools and processes (including Composer and Git), you should verify that
   you have the necessary :doc:`skillset </blt/developer/skills/>` to develop
   with Acquia BLT.


.. _blt-onboarding-initial-set-up:

Initial set up
--------------

#.  `Fork <https://help.github.com/articles/fork-a-repo>`__ the primary GitHub
    repository for the project you are developing.

#.  Clone your fork to your local computer. By convention, Acquia BLT refers
    to your fork as ``origin`` and the primary repository as ``upstream``):

    .. code-block:: bash

          git clone git@github.com:username/project-repo.git
          git remote add upstream git@github.com:acquia-pso/project-repo.git

#.  If your project uses separate ``master`` and ``develop`` branches,
    checkout the ``develop`` branch:

    .. code-block:: bash

          git checkout develop

#.  With Composer already installed, run the following command:

    .. code-block:: bash

          composer install

#.  Install ``blt`` alias:

    .. code-block:: bash

          ./vendor/bin/blt blt:init:shell-alias

    At this point, restart your shell for the alias work.

If your project uses a virtual development environment (such as
:ref:`Drupal VM <blt-drupal-vm-blt-projects>`), complete the following steps:

#.  Start the VM:

    .. code-block:: bash

          vagrant up

#.  SSH into the VM:

    .. code-block:: bash

          vagrant ssh

#.  Build and install the Drupal installation:

    .. code-block:: bash

          blt setup

If your project does not use a virtual development environment, complete the
following steps:

#.  Set up your local LAMP stack with the web root pointing at your project's
    ``docroot`` directory.
#.  Run the following command:

    .. code-block:: bash

          blt blt:init:settings

    This will generate ``docroot/sites/default/settings/local.settings.php``
    and ``docroot/sites/default/local.drush.yml``. Update these with your
    local database credentials and your local website URL.

#.  Run the following command:

    .. code-block:: bash

          blt setup

    This command will both build all project dependencies and install Drupal.

For more information about setting up a local \*AMP stack or virtual
development environment, see :doc:`/blt/install/local-development/`.


.. _blt-onboarding-ongoing-development:

Ongoing development
-------------------

As development progresses, you can use the following commands to keep your
local environment up to date:

-  Run ``blt setup`` to rebuild the codebase and reinstall your Drupal website
   (most commonly used early in development).
-  Run ``blt drupal:sync`` to rebuild the codebase, import a fresh database
   from a remote environment, and run schema and configuration updates (most
   commonly used later in development).

Each of these commands is a wrapper for several more granular commands that
can be run individually if desired. For instance, ``blt drupal:update`` runs
database updates and imports configuration changes. For a full list of
available project tasks, run ``blt``. For more information, see
:doc:`/blt/developer/project-tasks/`.

Local Git configuration
~~~~~~~~~~~~~~~~~~~~~~~

For readability of commit history, set your name and email address properly:

.. code-block:: bash

      git config user.name "Your Name"
      git config user.email your-email-address@example.com

Ensure that your local email address correctly matches the email address for
your JIRA account.


.. _blt-onboarding-updating-local-environment:

Updating your local environment
-------------------------------

The project is configured to update the local environment with a local Drush
alias and a remote alias as defined in ``blt/blt.yml`` or
``blt/local.blt.yml``. Given that these aliases match those in
``drush/sites/``, you can update the website with Acquia BLT.

:ref:`Local development tasks <blt-task-update-local>`


.. _blt-onboarding-github-configuration:

GitHub configuration
--------------------

To more easily identify developers in a project, be sure to set a name and
profile picture in your GitHub profile.

When working with GitHub, the `hub <https://github.com/github/hub>`__ utility
can be helpful when managing forks and pull requests. Installing hub can
depend on your local environment, so follow the `installation
instructions <https://github.com/github/hub#installation>`__ accordingly.


Next steps
----------

Review Acquia BLT :doc:`developer </blt/developer/>` and :doc:`technical
architect </blt/tech-architect/>` documentation by role to learn how to
perform common project tasks and integrate with third party tools.

.. Next review date 20200422
