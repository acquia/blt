.. include:: ../../common/global.rst

Continuous integration
======================

Continuous integration (CI) is a software engineering best practice and should
be a part of any Drupal project. Acquia BLT's automation commands perform
common tasks such as site installs, tests, and artifact builds consistently
across local, CI, development, and production environments. A robust CI process
using Acquia BLT's commands is critical to testing, building, and deploying
your project consistently.

.. _blt-ci-workflow:

Workflow
--------

The typical CI workflow is as follows:

#. A pull request or commit to GitHub triggers a build on your CI platform.
#. The CI platform reads and executes a series of build steps based on a script
   stored in your repository root directory. The build steps execute Acquia BLT
   commands to build and test the application incorporating changes from the
   pull request.

   The CI scripts provided out of the box by BLT perform the following tasks:

   -  Configure the build environment to match the hosting environment.
   -  Install dependencies (using `Composer <https://getcomposer.org/>`__ and,
      if configured, a frontend dependency manager such as NPM).
   -  Lint and validate code (using tools like
      `PHPCS <https://github.com/squizlabs/PHP_CodeSniffer>`__). As of BLT 11,
      validation also includes testing for deprecated code using the
      `Drupal Check <https://github.com/mglaman/drupal-check>`__ library.
   -  Install Drupal and import configuration using the chosen `configuration
      management strategy <https://docs.acquia.com/blt/developer/configuration-management/>`__.
   -  Run `automated tests <https://docs.acquia.com/blt/developer/testing/>`__
      (using tools like `PHPUnit <https://phpunit.de/>`__, and
      `Behat <https://docs.behat.org/en/latest/>`__) on the installed Drupal
      site.

#. The CI platform reports the status of the build (success or failure) back
   to GitHub.
#. If the build is successful, a code reviewer merges the pull request.
#. The merge triggers another CI job, testing your application again and
   generating an artifact suitable for deployment.
#. The CI platform pushes the artifact to a cloud hosting repository.

Out of the box, Acquia BLT handles the deployment of code artifacts to hosting
repositories, but doesn't interact with hosting providers to deploy code to
specific environments. You can add such custom deployment steps to
Acquia BLT's CI scripts to create a full *Continuous Deployment* workflow.

.. _blt-supported-platforms:

Supported CI platforms
----------------------

Acquia BLT natively supports the following CI platforms:

* :ref:`Acquia Cloud Pipelines <blt-ci-pipelines>`
* :ref:`Travis CI <blt-ci-travis-ci>`

The `Acquia BLT Plugins
<https://support.acquia.com/hc/en-us/articles/360046918614-Acquia-BLT-Plugins/>`__
page lists a number of community-developed plugins providing support for other
CI platforms.

Acquia BLT provides a template script file (such as ``.travis.yml`` or
``acquia-pipelines.yml``) for each of the CI platforms, allowing you to
quickly have a working build that follows the default steps outlined above.
You can generate this template script file for your project using the commands
detailed in the following sections.

You can customize the template script files. Acquia will continuously update
the default script files, but merging those updates into your customized files
is your responsibility.

.. _blt-ci-pipelines:

Acquia Cloud Pipelines
~~~~~~~~~~~~~~~~~~~~~~

:doc:`Acquia Cloud Pipelines
</acquia-cloud/develop/pipelines/>` is a continuous integration and continuous
deployment solution built on the Acquia Cloud infrastructure. For Acquia Cloud
users, Pipelines provides the benefit of integrating directly with
an Acquia Cloud subscription, which allows you to deploy build artifacts with
less effort.

To initialize Acquia Cloud Pipelines support for your Acquia BLT project,
complete the following steps:

#. :doc:`Connect Acquia Cloud Pipelines
   </acquia-cloud/develop/pipelines/connect/>` to your GitHub or Bitbucket
   repository

#. Initialize Acquia Cloud Pipelines for your project:

   .. code-block:: bash

      blt recipes:ci:pipelines:init

   The preceding code will generate an :doc:`acquia-pipelines.yml file
   </acquia-cloud/develop/pipelines/yaml/>` in your project root based on
   `BLT's default acquia-pipelines.yml file
   <https://github.com/acquia/blt/blob/10.x/scripts/pipelines/acquia-pipelines.yml>`__.

#. Change the :doc:`acquia-pipelines.yml file
   </acquia-cloud/develop/pipelines/yaml/>` to
   :doc:`specify which databases to copy
   </acquia-cloud/develop/pipelines/databases/>` into CDEs on deployment.

#. Commit and push the new file to your Acquia Git remote using commands such
   as the following:

   .. code-block:: bash

      git add acquia-pipelines.yml
      git commit -m 'Initializing pipelines integration.'
      git push origin

#. Submit a pull request to your GitHub repository.

Your new pull request will trigger a pipelines build to begin. The pull
request's web page reflects the pipelines build status. If merged, the
pipelines feature will generate a new branch on your Acquia Cloud
subscription named ``pipelines-[source-branch]-build``. The branch will
contain a deployment artifact deployable to an Acquia environment.

You can use the Acquia Cloud Pipelines user interface or :doc:`the Pipelines
CLI client </acquia-cloud/develop/pipelines/cli/install/>` to review the
status or logs for your build.

If you encounter problems, see
:doc:`/acquia-cloud/develop/pipelines/troubleshooting/`.


.. _blt-ci-travis-ci:

Travis CI
~~~~~~~~~

`Travis CI <https://travis-ci.com/>`__ is a continuous integration and
continuous deployment solution. Travis can integrate with Acquia Cloud,
but requires more initial configuration work than the Acquia Cloud pipelines
feature.

You must configure Acquia Cloud, GitHub, and Travis CI to work together using
the following steps:

.. note::

   The following instructions apply to private GitHub repositories and may have
   security implications for public repositories.

#. Initialize Travis CI support for your project by running the following
   command:

   .. code-block:: bash

      blt recipes:ci:travis:init

#. Run the following command to generate an SSH key locally so Travis can
   authenticate to Acquia Cloud:

   .. code-block:: bash

      cd ~/.ssh
      ssh-keygen -t rsa -b 4096 -m PEM

   Don't use a passphrase. Give the SSH key a unique name (such as *travis*).

   Due to Travis requiring legacy RSA PEM keys, you must explicitly define
   the format with the ``-m`` flag.

#. Create a new Acquia Cloud account used primarily as a container for the SSH
   keys granting Travis push access to Acquia Cloud. You can create a new
   account by inviting a new team member on **Teams** in Acquia Cloud using an
   email address such as ``<email>+<project>.travis@acquia.com``. The team
   member must have SSH push access with the *Team Lead* role. Acquia doesn't
   recommend using a personal account or re-using the shell account across
   projects posing a security risk, and causing deployments to fail if your
   account is removed from the project.

#. Sign in to the new Acquia Cloud account and add the public SSH key from the
   key pair generated in the preceding step by editing the profile and then
   clicking **Credentials**.

#. Add the same public SSH key to the Deployment Keys section on your
   project's GitHub settings page, located at
   ``https://github.com/acquia-pso/[project-name]/settings/keys``.

   .. note::

      If you don't have administrative control over your repository, you
      can't have direct access to the deployment keys settings on GitHub.

#. Add the private SSH key to your project's Travis CI settings located at
   ``https://magnum.travis-ci.com/acquia-pso/[project-name]/settings``.

#. Add your Acquia Cloud Git repository to the remotes section of your
   ``blt.yml`` file by running the following command and replacing
   ``[example]`` with your Git repository information:

   .. code-block:: text

      remotes:
         - [example]@svn-14671.prod.hosting.acquia.com:[example].git

#. Add your Acquia Cloud Git repository's server host name to
   ``ssh_known_hosts`` in your ``.travis.yml`` file. Use only the host name and
   don't include the user name and file name (example.git):

   .. code-block:: text

      addons:
        ssh_known_hosts:
        - svn-14671.prod.hosting.acquia.com

   .. note::

      If you are planning to run any ``drush sql-syncs`` or ``drush sql-rsync``
      commands between Acquia Cloud and your environment, be sure to add the
      test or stage server host to the preceding code.

Commits or merges to the develop branch on GitHub will now trigger a
fully built artifact deployed to your specified remotes.

You can watch several branches on GitHub for deployment (for example,
master, and integration) by adding one or more ``provider`` block to the deploy
section of your project's ``.travis`` file.

.. code-block:: text

   deploy:
      - provider: script
        script: "$BLT_DIR/scripts/travis/deploy_branch"
        skip_cleanup: true
        on:
          branch: integration

For information about manually deploying your project, see
:doc:`/blt/tech-architect/deploy/`.


.. Next review date 20200419
