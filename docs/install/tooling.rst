.. include:: ../../common/global.rst

Acquia BLT tooling
==================

The following is a list of operating system-level tools that Acquia BLT uses.

Not all of these are absolutely required. Some are required only when using
certain features of Acquia BLT, such as Selenium-based testing or Drupal VM
integration.

System-level packages
---------------------

For installation instructions, see :doc:`/blt/install/`.

.. list-table::
   :widths: 20 15 65
   :header-rows: 1
   :class: verticaltable

   * - Tool
     - Required
     - Purpose
   * - PHP
     - Yes
     - Required by several tools, including Composer, Drush, Robo, and Drupal.
       Ensure the following recommendations are met:

       .. tabs::

          .. tab:: 10.x

             -  Acquia BLT requires PHP 7.1 or greater. You can determine your
                existing version by running ``php -v``.

          .. tab:: 9.2.x

             -  Acquia BLT requires PHP 5.6 or greater. You can determine your
                existing version by running ``php -v``.
             -  The ``memory_limit`` for PHP is set to 2 GB or higher (for
                Composer). You can find the ``php.ini`` file for your PHP CLI
                by executing ``php --ini`` and looking for the ``Loaded
                Configuration file``.

   * - Composer
     - Yes
     - Used to manage project-level dependencies for Acquia BLT and Drupal.
       Composer is the default package manager for the PHP community, and
       is also used by Drupal core. |br|
       For information about using Composer in conjunction with Acquia BLT,
       see :doc:`/blt/developer/dependency-management/`. |br|
       You can update to the latest version of Composer by using the following
       command: ``composer self-update``
   * - `Git <https://git-scm.com/>`__
     - Yes
     - A distributed version control system. It is the VCS tool for the
       Drupal community.
   * - `Drush <https://www.drush.org/>`__
     - Yes
     - Command line shell and Unix scripting interface for Drupal. Acquia BLT
       uses Drush to communicate with Drupal from the command line. |br|
       *Drush is both a system level and a project level dependency*, which is
       unusual. It is possible to have one version of Drush
       on your computer and a different version of Drush used in your project
       directory. This is useful but frequently causes confusion. |br|
       Drush uses a special *launcher* script to look for a copy of Drush that
       is specific to your project. BLT ships such project-level drush binary
       in the ``vendor/bin`` directory of your project. Your global Drush
       installation defers to the project level binary when executing
       ``drush`` from an Acquia BLT project directory.
   * - `Java <https://www.java.com/en/>`__
     - No
     - Required by Selenium to communicate with Chrome. Selenium is one option
       for executing JavaScript Behat tests. For more information, see
       :doc:`/blt/developer/testing/`.
   * - `ChromeDriver <http://chromedriver.chromium.org/>`__
     - No
     - Required by Selenium to communicate with Chrome. Selenium is one option
       for executing JavaScript Behat tests. For more information, see
       :doc:`/blt/developer/testing/`.
   * - `Ansible <https://www.ansible.com/>`__
     - No
     - Required by `Drupal VM <https://www.drupalvm.com/>`__, which is one
       option for local development.
   * - `Vagrant <http://vagrantup.com/>`__
     - No
     - Required by `Drupal VM <https://www.drupalvm.com/>`__, which is one
       option for local development.
   * - `VirtualBox <https://www.virtualbox.org/wiki/VirtualBox>`__
     - No
     - Required by `Drupal VM <https://www.drupalvm.com/>`__, which is one
       option for local development.
   * - `Yarn <https://github.com/yarnpkg/yarn>`__
     - No
     - A package manager for JavaScript. Required by the `Cog
       <https://github.com/acquia-pso/cog>`__ Drupal base theme option.
   * - `nvm <https://github.com/creationix/nvm>`__
     - No
     - Manages multiple versions of NodeJS on a single computer. Required by
       the `Cog <https://github.com/acquia-pso/cog>`__ Drupal base theme
       option.


.. _blt-validation-testing-tools:

Validation and testing tools
----------------------------

The following tools are installed by Acquia BLT with Composer:

-  Behat
-  PHPUnit
-  PHP Code Sniffer


.. _blt-local-environments:

Local environments
------------------

Although you can use *any* Drupal-compatible local development environment
with Acquia BLT, specific support is provided for the following tools:

-  :doc:`/dev-desktop/`
-  Drupal VM

For more information, see :doc:`/blt/install/local-development/`.


.. _blt-ci-cd-solutions:

Continuous integration and continuous delivery solutions
--------------------------------------------------------

Although you can use *any* continuous integration (CI) or continuous
delivery (CD) tool with Acquia BLT, specific support (in the form of default
configuration files) is provided for the following tools:

-  :doc:`Acquia Cloud pipelines feature </acquia-cloud/develop/pipelines/>`
-  Travis CI

For more information, see :doc:`/blt/tech-architect/ci/`.


.. _blt-hosting:

Hosting
-------

Although you can host an Acquia BLT project in *any* Drupal-compatible hosting
environment, specific support is provided for both Acquia Cloud and
Acquia Cloud Site Factory with the following services:

-  Providing Cloud hooks.
-  Providing Acquia-specific default configuration in ``settings.php``.
-  Structuring project directories to match Acquia Cloud repository's
   default structure.


.. _blt-headless-chrome:

Headless Chrome
---------------

Headless Chrome is used by default for Behat tests, although you can also use
Selenium or PhantomJS.

.. admonition:: Note for Docker users

   Connections to Headless Chrome will occasionally time out in containerized
   environments, such as Docker. For discussion and a possible solution, see
   this `issue on GitHub <https://github.com/acquia/blt/issues/2083>`__.

.. Next review date 20200422
