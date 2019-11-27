.. include:: ../../common/global.rst

Deployment workflow
===================

This document outlines the workflow to build a complete Drupal application
(plus supporting features, such as Cloud Hooks) which can be deployed directly
to Acquia Cloud. Collectively, this bundle of code is referred to as the *build
artifact*.

The most important thing to remember about this workflow is that the GitHub
and Acquia Cloud repositories are not clones of one another. GitHub only
stores the source code, and Acquia Cloud only stores the production code (for
example, the build artifacts).

Currently, this workflow can either be followed manually, or integrated into
a continuous integration (CI) solution such as the
:doc:`Acquia Cloud pipelines feature </acquia-cloud/develop/pipelines/>`,
Travis CI, or Jenkins.


First time setup
----------------

You should have your GitHub repository checked out locally. Your Acquia Cloud
repository should be empty, or nearly empty.

Check out a new branch to match whatever branch you are working on in GitHub
(typically ``develop``).

Ensure your Acquia Cloud remote is listed in ``blt.yml`` under
``git:remotes``. For example:

.. code-block:: text

      git:
        default_branch: master
        remotes:
          cloud: 'project@svn-1234.devcloud.hosting.acquia.com:project.git'


.. _arch-creating-the-build-artifact:

Creating the build artifact
---------------------------

In order to create the build artifact in ``/deploy``, run the following
command:

.. code-block:: bash

      blt artifact:build

This task is analogous to ``source:build`` but with a few critical
differences:

-  The docroot is created at ``/deploy/docroot``.
-  Only production required to the docroot
-  *(planned)* CSS and JavaScript are compiled in production mode (compressed
   and minified)
-  *(planned)* Sensitive files, such as ``CHANGELOG.txt``, are removed.

After the artifact is created, you can inspect it or even run it as a website
locally. You may also manually commit and push it to Acquia Cloud.


.. _arch-create-and-deploy-the-build-artifact:

Create and deploy the build artifact
------------------------------------

To both create and deploy the build artifact in a single command, run the
following command:

.. code-block:: bash

      blt artifact:deploy --commit-msg "BLT-000: Example deploy to branch" --branch "develop-build" --no-interaction

This command will commit the artifact to the ``develop-build`` branch with
the specified commit message and push it to the remotes defined in
``blt.yml``.

To create a new Git tag for the artifact (rather than committing to a branch)
run the following command:

.. code-block:: bash

      blt artifact:deploy --commit-msg "Creating release 1.0.0." --tag "1.0.0"

This will generate the artifact, tag it with ``1.0.0``, and push it to the
remotes defined in ``blt.yml``.

When deploying a tag to the artifact repository, if the config option
``deploy.tag_source`` is set to ``TRUE``, Acquia BLT will also create the
supplied tag on the source repository. This makes it easier to verify the
source commit upon which an artifact tag is based.

.. note::

      Acquia BLT does not push the tag created on the source repository to its
      remote.


Modifying the artifact
----------------------

The artifact is built by running the ``artifact:build`` target, which does the
following:

-  Rsyncs files from the repository root.
-  Re-builds dependencies directly in the deploy directory (for example,
   ``composer install``).

The rsync and re-build processes can be configured by modifying the values of
variables under the top-level ``deploy`` key in your ``blt.yml`` file.

For more information about overriding default configuration, see
:doc:`/blt/extending-blt/`.

Debugging deployment artifacts
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you want to create, commit, but *not push* the artifact, you can create a
test run with the following command:

.. code-block:: bash

      blt artifact:deploy --dry-run

This is helpful for debugging deployment artifacts.


Continuous integration
----------------------

Instead of performing these deployments manually, you can enlist the help of a
CI tool, such as the Acquia Cloud pipelines feature, Travis CI, or Jenkins.
This will allow you to generate deployment artifacts whenever code is merged
into a given branch. For information about configuring a CI tool,
see :doc:`/blt/tech-architect/ci/`.


Cloud Hooks
-----------

On Acquia Cloud, :doc:`Cloud Hooks </acquia-cloud/develop/api/cloud-hooks/>`
are the preferred method to run database updates and configuration imports on
each deploy. Acquia BLT provides a post-code-deploy hook that will run these
updates and fail the deployment task in Insight if anything goes wrong.

To install Acquia Cloud hooks for your Acquia BLT project, complete the
following steps:

#.  Initialize Acquia Cloud hooks by running the following command:

    .. code-block:: bash

          blt recipes:cloud-hooks:init

    This will add a hooks directory in your project root based on `Acquia
    BLT's default Acquia Cloud hooks
    <https://github.com/acquia/blt/tree/10.x/scripts/cloud-hooks/hooks>`__.

#.  Commit the new directory and push it to your Acquia Git remote. Refer to
    the following example commands:

    .. code-block:: bash

          git add hooks
          git commit -m 'Initializing Acquia Cloud hooks.'
          git push origin

For consistency and reliability, you should run the same updates on deployment
as you would run locally or in CI testing. Acquia BLT provides aliases for the
``drupal:update`` task to support this in a local environment and
``artifact:update:drupal`` to execute against an artifact.

If your team uses Slack, you can also be notified of each successful or failed
deployment. Set up an incoming webhook in your Slack team to receive the
notification (see the `Slack API documentation <https://api.slack.com/>`__ for
more information), and then store the webhook URL in ``slack.webhook-url`` in
``blt/blt.yml``. You may also set it as an environmental variable
``SLACK_WEBHOOK_URL``.

For more information, see the `Acquia Cloud Hooks Slack example
<https://github.com/acquia/cloud-hooks/tree/master/samples/slack>`__.

.. Next review date 20200422
