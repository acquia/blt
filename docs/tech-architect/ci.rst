.. include:: ../../common/global.rst

Continuous integration
======================

Continuous integration (CI) is a software engineering best practice and should
be a part of any Drupal project. Acquia BLT's automation commands ensure
that (when used properly) local development, CI, and
deployment all execute the same commands in the same order (which is
critical to ensure that a success or failure in lower environments
aligns with CI and/or deployment successes and failures).

.. _blt-ci-workflow:

Workflow
--------

The typical CI workflow is as follows:

#. A pull request or commit to GitHub triggers a CI build.
#. The CI tool reads and executes a series of build steps based on a script
   (usually contained in an script file inside the repo). The build steps
   execute Acquia BLT commands to build and test the application against all
   changes made in the pull request.

By default a BLT build will include the following steps for CI:

   - Configure the build environment to closely match the hosting environment
   -  Install dependencies (using `Composer <https://getcomposer.org/>`__ and, if
      configured, a frontend dependency manager such as NPM).
   -  Lint and validate code (using tools like
      `PHPCS <https://github.com/squizlabs/PHP_CodeSniffer>`__). Note that as of BLT 11,
      validation also includes testing for deprecated code using the
      `Drupal Check <https://github.com/mglaman/drupal-check>`__ library.
   -  Install Drupal and import configuration using the chosen configuration
      management strategy (if any, defaults to Config Split)
   -  Run `automated tests <https://docs.acquia.com/blt/developer/testing/>`__
      (using tools like `PHPUnit <https://phpunit.de/>`__, and
      `Behat <https://docs.behat.org/en/latest/>`__) against the installed
      instance of Drupal.

Each of these steps is intended to ascertain if anything in the pull request
fundamentally changes the stability / functionality of a stable codebase.

Once the build has concluded:

#. The CI tool reports the status of the build (success or failure) back
   to GitHub.
#. If the build is successful, the pull request can be manually merged (if
   not, the developer should revisit and resolve any failures).
#. The merge triggers another CI build which follows the same steps as above
   to build and test the application as well as generating an artifact
   suitable for deployment.
#. The CI tool deploys the artifact to the hosting git repository. This step
   is known as *Continuous Deployment (CD).*

.. _blt-supported-solutions:

Supported CI Solutions
--------

Acquia BLT natively supports the following CI solutions:

* :ref:`Acquia pipelines <blt-ci-pipelines>`
* :ref:`Travis CI <blt-ci-travis-ci>`

There are also a number of community developed plugins for other CI
tools. These can be found on the `BLT plugins page <https://docs.acquia.com/blt/plugins/>`__.

Acquia BLT provides one default script file (such as ``.travis.yml`` or
``acquia-pipelines.yml``) for each of the CI solutions, allowing you to
quickly have a working build (which uses the default steps outlined above.)
To use the default script file, you must run the
:ref:`following initialization command <blt-ci-workflow>`. The command
will copy the default script file to the required location, in the same
manner that Drupal requires you to copy ``default.settings.php`` to
``settings.php.``

The script files are intended for customization. Acquia will provide
updates to the default script files, but merging those updates into your
customized files is your responsibility.


.. _blt-ci-pipelines:

Acquia Cloud pipelines feature
------------------------------

The :doc:`Acquia Cloud pipelines feature
</acquia-cloud/develop/pipelines/>` is a continuous integration and continuous
deployment solution built on the Acquia Cloud infrastructure. For Acquia Cloud
users, the pipelines feature provides the benefit of integrating directly with
an Acquia Cloud subscription, which allows you to deploy build artifacts with
less effort.

To initialize pipelines feature support for your Acquia BLT project, complete
the following steps:

#. :doc:`Connect the pipelines service
   </acquia-cloud/develop/pipelines/connect/>` to your GitHub or Bitbucket
   repository

#. Initialize pipelines for your project by running the following command:

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

#. Commit the new file, and push then it to your Acquia Git remote by using
   commands based on the following example:

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

Additional information
~~~~~~~~~~~~~~~~~~~~~~

You can use the Acquia Cloud pipelines user interface or :doc:`the pipelines
CLI client </acquia-cloud/develop/pipelines/cli/install/>` to review the
status or logs for your build.

If you encounter problems, see
:doc:`/acquia-cloud/develop/pipelines/troubleshooting/`.


.. _blt-ci-travis-ci:

Travis CI
---------

`Travis CI <https://travis-ci.com/>`__ is a continuous integration and
continuous deployment solution. Travis can integrate with Acquia Cloud,
but requires more initial configuration work than the Acquia Cloud pipelines
feature.

Configuring Travis CI for automated deployments
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You must configure Acquia Cloud, GitHub, and Travis CI to work together to
configure the :ref:`workflow <blt-ci-workflow>`. To do this, complete the
following steps:

.. note::

   The following instructions apply only to private GitHub repositories.

#. Initialize Travis CI support for your project by running the following
   command:

   .. code-block:: bash

      blt recipes:ci:travis:init

#. Run the following command to generate an SSH key locally to allow Travis
   to authenticate to Acquia Cloud:

   .. code-block:: bash

      cd ~/.ssh
      ssh-keygen -t rsa -b 4096 -m PEM

   Do not use a passphrase. Instead, name the SSH key something different than
   your normal Acquia Cloud key (such as *travis*).

   Due to Travis requiring a legacy RSA PEM keys, you must explicitly define
   the format with the ``-m`` flag.

#. Create a new Acquia Cloud account used primarily as a container for the SSH
   keys granting Travis push access to Acquia Cloud. You can create a new
   account by inviting a new team member on **Teams** in Acquia Cloud using an
   email address such as ``<email>+<project>.travis@acquia.com``. The team
   member must have SSH push access with the *Team Lead* role. Acquia does not
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

      If you do not have administrative control over your repository, you
      cannot have direct access to the deployment keys settings on GitHub.

#. Add the private SSH key to your project's Travis CI settings located at
   ``https://magnum.travis-ci.com/acquia-pso/[project-name]/settings``.

#. Add your Acquia Cloud Git repository to the remotes section of your
   ``blt.yml`` file by running the following command and replacing
   ``[example]`` with your Git repository information:

   .. code-block:: text

      remotes:
         - [example]@svn-14671.prod.hosting.acquia.com:[example].git

#. Add your Acquia Cloud Git repository's server host name to
   ``ssh_known_hosts`` in your ``.travis.yml`` file. Ensure you remove the
   user name and file name (example.git) and use only the host name:

   .. code-block:: text

      addons:
        ssh_known_hosts:
        - svn-14671.prod.hosting.acquia.com

   .. note::

      If you are planning to run any ``drush sql-syncs`` or ``drush sql-rsync``
      commands between Acquia Cloud and your environment, be sure to add the
      test or stage server host to the preceding code.

Commits or merges to the develop branch on GitHub will now trigger a
fully-built artifact deployed to your specified remotes.

For information about manually deploying your project, see
:doc:`/blt/tech-architect/deploy/`.

Configuring Travis CI for automated deployments on several branches
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can watch several branches on GitHub for deployment (for example,
master, and integration) by adding another ``provider`` block to the deploy
section of your project's ``.travis`` file as indicated in the following
code. You can add several provider blocks, as needed.

.. code-block:: text

   deploy:
      - provider: script
        script: "$BLT_DIR/scripts/travis/deploy_branch"
        skip_cleanup: true
        on:
          branch: integration

.. Next review date 20200419
