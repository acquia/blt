.. include:: ../../common/global.rst

Development workflow with Acquia BLT
====================================

This page contains information about how you can contribute code to an
Acquia BLT project.


.. _blt-git-workflow:

Git workflow
------------

Do not push direct changes to the ``build-artifact`` repository. The
process of syncing the repositories is managed transparently in the
background.

Recommended Git workflows to consider, depending on your team's size, include
the following options:

*  Feature branch workflow
*  Gitflow
*  Gitflow (abridged)


.. _blt-git-feature-branch-workflow:

Feature branch workflow
~~~~~~~~~~~~~~~~~~~~~~~

The `Feature branch workflow
<https://www.atlassian.com/git/tutorials/comparing-workflows#feature-branch-workflow>`__
encourages all feature development work to take place on a dedicated branch,
instead of committing locally to the standard ``master`` branch.

Workflows of this type have the following attributes:

-  A developer creates a new branch based on an up-to-date ``master``
   branch to start work on a new feature.
-  When the developer completes the work, they push the *feature branch* to
   ``origin`` (or whatever name the developer gave the remote of their forked
   repository).
-  The developer opens a pull request against the ``master`` branch, giving
   other team members the chance to review the work completed before merging
   into ``master``.
-  After the work is accepted, the developer merges the work into the
   ``master`` branch.

The preceding flow is best-suited for a small team. For larger teams,
consider using the Gitflow workflow.


.. _blt-gitflow-workflow:

Gitflow workflow
~~~~~~~~~~~~~~~~

The `Gitflow workflow
<https://www.atlassian.com/git/tutorials/comparing-workflows#gitflow-workflow>`__
builds on the concept of the feature branch workflow. Developers commit to
feature branches instead of directly to ``master``, and they will submit pull
requests against a ``develop`` branch serving as an integration branch for new
features.

The Gitflow specifics are as follows:

-  A developer creates a new branch based on an up-to-date ``develop``
   branch to start work on a new feature.
-  When the developer completes the work, they push the *feature branch* to
   ``origin``.
-  The developer opens a pull request against the ``develop`` branch, giving
   other team members the chance to review the work completed before merging
   into ``develop``.
-  After the developer merges the feature group into the ``develop``
   branch, or as a pre-determined release date approaches, the developer
   creates a new ``release`` branch off of ``develop``.
-  From then on, the team or release master works the ``release`` branch to
   add only what is necessary for the release, while the rest of the team
   continues feature development against the ``develop`` branch.
-  The release master merges the ``release`` branch into ``master``, and
   rebases ``develop`` onto ``master`` upon merging.

Gitflow workflow allows a larger team to work off an integrated branch
(``develop``), while maintaining a stable ``master`` branch remaining in a
good state.

.. _blt-gitflow-abridged:

Gitflow workflow (abridged version)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The `Gitflow workflow
<https://www.atlassian.com/git/workflows#!workflow-gitflow>`__ builds on
the concept of the feature branch workflow. Developers commit to feature
branches instead of directly to ``master``, and they will submit pull requests
against a ``develop`` branch, serving as an integration branch for new
features. The Gitflow workflow (abridged) specifics are as follows:

-  A developer creates a new branch based on an up-to-date ``develop``
   branch to start work on a new feature.
-  When the developer completes the work, they push the *feature branch*
   to ``origin``.
-  The developer opens a pull request against the ``develop`` branch, giving
   other team members the chance to review the work completed before merging
   into ``develop``.
-  After merging the feature group into the ``develop`` branch, and
   determining the branch is in a good state, the developer merges into the
   ``master`` branch.

The Gitflow workflow (abridged) still allows a team to work off an integrated
branch (``develop``), while maintaining a stable ``master`` branch, but
removes the ``release`` branch component of the flow.

.. note::

   In all preceding workflows, a developer or team merges hotfixes directly
   into a ``hotfix`` branch they can merge to ``master``.


.. _blt-local-dev-workflow-example:

Workflow example: Local development
-----------------------------------

#. Assign an issue to yourself in your issue tracking system (in this
   example, Atlassian JIRA).
#. Fetch upstream to ensure you are working with the most current code:

   .. code-block:: bash

          git fetch upstream

#. Create a new local feature branch (named according to the pattern
   ``abc-123-short-desc``, where ``ABC`` is the JIRA prefix of your JIRA
   project and ``123`` is the ticket number for the work), and then run the
   following command:

   .. code-block:: bash

          git checkout -b abc-123-short-desc upstream/master

#. Reset your local environment (if necessary) to a clean state by running
   one of the following commands:

   *  .. code-block:: bash

           blt setup

   *  .. code-block:: bash

           blt sync

#. Make your code changes.
#. Commit your changes to the repository. Each commit must be logically
   atomic, and your commit messages should use a pattern similar to
   ``ABC-123 A grammatically correct sentence ending within punctuation.``
#. Run tests and validation scripts with the following commands:

   .. code-block:: bash

          blt validate
          blt tests

#. Ensure you make no added changes to the upstream repository by running the
   following command:

   .. code-block:: bash

      git fetch upstream

   If necessary, rebase by running the following command:

   .. code-block:: bash

      git rebase upstream/master

#. Push your work to your forked repository (origin) by running the following
   command:

   .. code-block:: bash

      git push --set-upstream origin abc-123-short-desc

#. Create a pull request.


.. _blt-creating-pr:

Creating a pull request
-----------------------

Pull requests must never contain merge commits from upstream changes. To
avoid merge commits from upstream changes, use the ``git rebase`` command
instead of pulling and merging.

Push your feature branch to your fork of the upstream repository, and then
submit a pull request from ``your-fork/feature-branch`` to
``canonical-repo/develop``. You may optionally use `Hub
<https://github.com/github/hub>`__ to submit your pull request from the
command line with the following command:

.. code-block:: bash

   hub pull-request

To enforce consistency on a project, you can configure a pull request template
by running the following command:

.. code-block:: bash

   git config --global --add hub.pull-request-template-path ~/.pr-template


.. _blt-resolving-merge-conflicts:

Resolving merge conflicts
-------------------------

Merge conflicts result when several developers submit pull requests that
change the same code, and Git cannot resolve the conflict. If two developers
add update hooks to the same module at the same time, the changes conflict
because you must number update hooks in a defined sequence.

Developers are responsible for fixing merge conflicts on their own pull
requests.

Use the following process to resolve a merge conflict:

#. Fetch upstream history by running the following command:

   .. code-block:: bash

      git fetch upstream

#. Check out the branch based on your open pull request such as master, and
   run the following command:

   .. code-block:: bash

      git checkout master

#. Ensure your branch matches upstream by running the following command:

   .. code-block:: bash

      git reset --hard upstream/master

#. Check out your feature branch by running the following command:

   .. code-block:: bash

      git checkout feature/foo

#. Merge master by running the following command:

   .. code-block:: bash

      git merge master

   Git will display a message that indicates the existence of a merge
   conflict.

#. Run the following command to find any conflicting files:

   .. code-block:: bash

      git status

#. Edit the files to resolve the conflict.
#. Add all files that you have fixed by running the following command:

   .. code-block:: bash

      git add

#. Run the following command to finish the merge:

   .. code-block:: bash

      git commit

#. Complete the process by updating the pull request with the following
   command:

   .. code-block:: bash

      git push origin feature/foo


Additional resources
~~~~~~~~~~~~~~~~~~~~

-  `Resolve merge conflicts
   <https://confluence.atlassian.com/bitbucket/resolve-merge-conflicts-704414003.html>`__
-  `Resolving conflicts <https://githowto.com/resolving_conflicts>`__


.. _blt-integration-workflow:

Integration (merging pull requests)
-----------------------------------

Acquia recommends the use of the either the *integration manager* or the
*peer review* models of the integration workflow.

.. note::

   In the integration manager or peer review workflow, no one must ever commit
   their own code to the primary working branch.

.. _blt-integration-manager:

Integration manager
~~~~~~~~~~~~~~~~~~~

The integration manager model requires one (or more) lead developers to take
responsibility for merging all pull requests. Integration management ensures
consistency in quality control and identifies any potential issues with
related, open pull requests.

A small group is selected to be integrators who review all commits. If an
integrator performs work, fellow integrators must review all work as if they
were a developer.


.. _blt-peer-review:

Peer review
~~~~~~~~~~~

The peer review model removes the bottleneck of designated integrators, but
still eliminates commits directly to the working branch. A different
developer than the developer that submitted the original commit reviews every
commit.


.. _blt-dev-workflow-ci:

Continuous integration
----------------------

After a developer submits or merges a pull request, Acquia's continuous
integration (CI) solution builds a website artifact, installs an ephemeral
instance of Drupal, and runs tests against them.

For more information about the build process, see
:doc:`/blt/tech-architect/ci/`.


.. _blt-cloud-deployment:

Deploying code on Acquia Cloud
------------------------------

After work is merged on GitHub and tested though the continuous
integration solution, a separate production-ready built artifact is built
and deployed to Acquia Cloud. The building and deployment can be done
either manually or by using automation.

For more information, see :doc:`/blt/tech-architect/deploy/`.

.. _blt-release-process:

Release process
---------------

A designated Release Master will perform the release to production. The
release master is typically the project's technical architect.

For detailed information, see :doc:`/blt/tech-architect/release-process/`.

.. Next review date 20200422
