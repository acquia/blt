.. include:: ../../common/global.rst

Release process
===============

Use the resources on this documentation page to help you use BLT with your
release process.


.. _blt-release-branching-strategies:

Branching strategies
--------------------

For information about branching strategies, see the :ref:`Git workflow
section of the Dev Workflow document <blt-git-workflow>`.


.. _blt-generating-build-artifact:

Generating a build artifact
---------------------------

For information about generating a build artifact, see :ref:`Create and deploy
the build artifact <arch-create-and-deploy-the-build-artifact>`.


.. _blt-tagging:

Tagging
-------

Whenever the ``master`` branch contains all of the desired commits for a
release (regardless of the :ref:`Git workflow <blt-git-workflow>`
your team employed to arrive at the updated branch), you should create a
`tag <https://git-scm.com/book/en/v2/Git-Basics-Tagging>`__. Common practices
would have you use semantic versioning to name tags (for example, ``1.0.0``
or ``1.2.3``).

To create a tag, check out the ``master`` branch locally and then run the
``git tag`` command, similar to the following:

.. code-block:: text

   git checkout master
   git tag 1.0.0

If you have a :doc:`continuous integration </blt/tech-architect/ci/>`
environment that uses Travis CI or the Acquia Cloud pipelines feature,
whenever you push the *source tag* to your GitHub repository, an *artifact
tag* corresponding to your source tag will be created and pushed to Acquia
Cloud. The tag name will be name of the source tag with ``-build`` appended.
For example, a ``1.0.0`` source tag would make a tag named ``1.0.0-build``.

If you are doing deployments manually, you will want to checkout your
``master`` branch locally, and :ref:`manually build a deployment
artifact <arch-creating-the-build-artifact>` based off of that.
Even if you build the deployment artifact manually, the recommendation
is to still push up a source tag (for example, ``1.0.0``) based on the
``master`` branch in your repository.


.. _blt-deploying-tag-executing-updates:

Deploying tag and executing updates
-----------------------------------

Deploying Drupal across environments can be daunting, but if you use due
diligence with your configuration management, the process of deployment can
be straightforward.

Regardless of the number of environments or the versioning workflow in use,
the actual deployment process will occur similar to the following:

.. note::

   The following commands are examples.

#.  Enable maintenance mode for your website, using the following command:

    .. code-block:: text

       drush vset maintenance_mode 1

#.  Flush the website's caches to empty the cache tables and ensure
    maintenance mode is enabled:

    .. code-block:: text

       drush cc all

#.  Perform any necessary backups, including the database:

    .. code-block:: text

       drush sql-dump > backup-yyyy-mm-dd.sql

#.  Pull the latest code to the server:

    .. code-block:: text

       git pull origin/master

#.  Run ``update.php``.

    .. code-block:: text

       drush updb -y

#.  Disable maintenance mode for the website:

    .. code-block:: text

       drush vset maintenance_mode 0

#.  Clear the website's Drupal caches again:

    .. code-block:: text

       drush cc all

Be aware of the following suggestions for actions to avoid for your production
websites:

- **Do not revert all features using** ``drush fra -y`` – This command
  poses a website stability risk and also risks wiping a feature that may be
  been accidentally overridden in production. Feature should be explicitly
  reverted using a call to ``features_revert_module()`` in a
  ``hook_update_N()`` implementation.
- **Do not run** ``drush cc all``: Whenever possible, attempt to target
  specific caches.
- **Do not use** ``drush use``: This command introduces the risk that the
  release master will accidentally run a command against prod after the
  release.

Depending on the infrastructure and the extent of website changes, you may
need to compete some additional steps. For example, a major application change
may require a flush of other caches in the system, such as Varnish® or
Memcached.


.. _blt-deployment-notifications:

Notifications
-------------

You can configure several tools to provide notifications of deployment
related events, including the following:

-  `Travis CI <https://docs.travis-ci.com/user/notifications/>`__ can
   notify you about your build results using email, IRC, or webhooks.
-  Jenkins has plugins to provide build notifications using several services,
   including `Slack
   <https://wiki.jenkins-ci.org/display/JENKINS/Slack+Plugin>`__ and
   `IRC <https://wiki.jenkins-ci.org/display/JENKINS/IRC+Plugin>`__.
-  You can use :doc:`Acquia Cloud Hooks
   </acquia-cloud/develop/api/cloud-hooks/>` to provide deployment-,
   database-, or code-related notification to services, such as the following:

   -  New Relic
   -  Slack
   -  HipChat


Additional Resources
--------------------

-  `Connecting the Tubes: JIRA, GitHub, Jenkins, and Slack
   <https://dev.acquia.com/blog/connecting-tubes-jira-github-jenkins-and-slack>`__

.. Next review date 20200422
