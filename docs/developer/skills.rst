.. include:: ../../common/global.rst

Minimum skill set for using Acquia BLT
======================================

Acquia BLT is a tool that reaches far beyond Drupal development, and
developers planning on using it should have a working knowledge of
the following technologies to be successful.

For information about installing and using Acquia BLT, see
:doc:`/blt/install`. It is strongly urged that you use a Mac for local
development, although certain versions of Linux and Windows 10 can also be
used. Other operating systems **should not** be used for Acquia BLT
development.


.. _blt-enterprise-web-development:

Enterprise web development
--------------------------

Acquia BLT can be operated on any *LAMP* stack that will run Drupal.

Development environments
~~~~~~~~~~~~~~~~~~~~~~~~

-  `Drupal VM <https://www.drupalvm.com/>`__: The goal of this project is
   to make creating a local Drupal test and development environment
   quick and easy, and to introduce new developers to the world of Drupal
   development in local, virtual environments (instead of MAMP- or WAMP-based
   development).
-  :doc:`/dev-desktop/`: Acquia Dev Desktop is a free application that
   allows you to run and develop Drupal sites locally on your computer and
   optionally host them using Acquia Cloud. Use Acquia Dev Desktop to evaluate
   Drupal, add and test other Drupal modules, and develop sites while on a
   plane or away from an internet connection.

**Additional resources**

-  `Drupal VM Quickstart
   Guide <https://github.com/geerlingguy/drupal-vm#quick-start-guide>`__
-  :doc:`/blt/developer/onboarding/`

Dependency management
~~~~~~~~~~~~~~~~~~~~~

-  `Composer <https://getcomposer.org/>`__: Composer is a tool for
   dependency management in PHP. It allows you to declare the libraries your
   project depends on and it will manage (install and update) them for you.

**Additional resources**

-  `Getting Started with Composer <https://getcomposer.org/doc/00-intro.md>`__
-  :doc:`/blt/developer/dependency-management/`

**Common commands**

-  `composer install <https://getcomposer.org/doc/03-cli.md#install>`__
-  `composer update <https://getcomposer.org/doc/03-cli.md#update>`__
-  `composer require <https://getcomposer.org/doc/03-cli.md#require>`__


.. _blt-version-control:

Version control
---------------

-  `Git <https://git-scm.com>`__: Git is a version control system (VCS) for
   tracking changes in computer files and coordinating work on those files
   among multiple people. It is primarily used for software development, but
   it can be used to keep track of changes in any files.

**Additional resources**

-  `Getting Started with Git
   <https://git-scm.com/book/en/v2/Getting-Started-About-Version-Control>`__
-  :doc:`BLT Repository Architecture </blt/developer/repo-architecture/>`
-  :ref:`BLT Git Workflow <blt-git-workflow>`

**Common commands**

-  `git add <https://git-scm.com/docs/git-add>`__
-  `git add -p <https://git-scm.com/docs/git-add#git-add--p>`__
-  `git checkout  <https://git-scm.com/docs/git-checkout>`__
-  `git commit <https://git-scm.com/docs/git-commit>`__
-  `git commit –amend
   <https://git-scm.com/docs/git-commit#git-commit---amend>`__
-  `git push <https://git-scm.com/docs/git-push>`__
-  `git push -f <https://git-scm.com/docs/git-push#git-push--f>`__
-  `git rebase <https://git-scm.com/docs/git-rebase>`__
-  `git rebase -i
   <https://git-scm.com/docs/git-rebase#git-rebase---interactive>`__


.. _blt-deployment-management:

Deployment management
---------------------

-  `Travis CI <https://travis-ci.com/>`__: Travis CI is a hosted,
   distributed continuous integration service used to build and test software
   projects hosted at GitHub.

**Additional resources**

-  :ref:`Acquia BLT Travis CI documentation page <blt-ci-travis-ci>`


Additional and optional skills
------------------------------

-  GitHub: GitHub is a web-based Git repository hosting service. It offers
   all the distributed version control and source code management (SCM)
   functionality of Git and adds its own features. It provides access
   control and several collaboration features such as bug tracking, feature
   requests, task management, and wikis for every project.

   -  `Getting Started with GitHub
      <https://guides.github.com/activities/hello-world/>`__
   -  `Git Forks <https://help.github.com/articles/fork-a-repo/>`__
   -  `Understanding GitHub Flow
      <https://guides.github.com/introduction/flow/>`__
   -  `Pull Requests
      <https://help.github.com/articles/about-pull-requests/>`__
   -  `BLT GitHub Configuration <onboarding.md#github-configuration>`__

-  `Behat <http://behat.org>`__: Behat is an open source behavior-driven
   development framework for PHP. It is a tool to support you in delivering
   software that matters through continuous communication, deliberate
   discovery and test-automation.

   -  `Getting Started with Behat
      <http://behat.org/en/latest/quick_start.html>`__
   -  `Behat User Guide <http://behat.org/en/latest/user_guide.html>`__
   -  `BLT Automated Testing with Behat <testing.md#behat>`__
   -  `Behat Drupal Extension
      <https://www.drupal.org/project/drupalextension>`__
   -  `Behat Tags
      <http://behat.org/en/latest/user_guide/organizing.html>`__

-  `PHPunit <https://phpunit.de>`__: PHPUnit is a programmer-oriented
   testing framework for PHP.

   -  `Getting Started with PHPUnit
      <https://phpunit.de/getting-started.html>`__
   -  `PHPUnit documentation <https://phpunit.de/documentation.html>`__
   -  :ref:`Acquia BLT automated testing with PHPUnit <dev-blt-phpunit>`

-  `PHP <http://php.net>`__: PHP is a widely-used open source
   general-purpose scripting language that is especially suited for web
   development and can be embedded into HTML.

   -  `Intro to PHP <http://php.net/manual/en/intro-whatis.php>`__
   -  `Using xdebug to Debug PHP <https://xdebug.org/docs/>`__

   We also strongly recommend an IDE for PHP Development, such as one of the
   following:

   -  `PHPStorm <https://www.jetbrains.com/phpstorm/>`__
   -  `Netbeans <http://netbeans.org/features/php/>`__


.. _blt-front-end-technologies:

Front-end technologies
----------------------

These are technologies used by the `COG theme
<https://www.drupal.org/project/cog>`__ and while not directly part of
Acquia BLT, they are often integrated directly into Acquia BLT commands.

-  `Gulp <http://gulpjs.com/>`__: Gulp is a toolkit for automating painful
   or time-consuming tasks in your development workflow, so you can stop
   messing around and build something.

   -  `Getting Started with Gulp
      <https://github.com/gulpjs/gulp/blob/master/docs/getting-started.md>`__

   **Common commands**

   -  `gulp watch
      <https://github.com/gulpjs/gulp/blob/master/docs/API.md#gulpwatchglob--opts-tasks-or-gulpwatchglob--opts-cb>`__

-  `SASS <http://sass-lang.com/>`__

   -  `SASS documentation
      <http://sass-lang.com/documentation/file.SASS_REFERENCE.html>`__
   -  :doc:`/blt/developer/frontend/`

-  `NPM <https://www.npmjs.com/>`__: npm is the package manager for
   JavaScript. Find, share, and reuse packages of code from hundreds of
   thousands of developers, and assemble them in powerful new ways.

   -  `Getting Started with NPM <https://docs.npmjs.com/>`__
   -  :ref:`Acquia BLT front-end dependencies <blt-front-end-dependencies>`

   **Common Commands**

   -  `npm install
      <https://docs.npmjs.com/getting-started/installing-npm-packages-locally>`__
   -  ``npm run install-tools``
   -  ``npm run build``

.. Next review date 20200422
