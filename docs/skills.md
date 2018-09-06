# Minimum Skillset for Using BLT
BLT is a tool that reaches far beyond Drupal development, and because of this developers planning on using it should working knowledge of the following technologies in order to be successful.

See the [System Requirements](INSTALL.md) for installing / using BLT. It is strongly urged that you use a Mac for local development, although certain versions of Linux and Windows 10 can also be used. Other operating systems ***should not*** be used for BLT development.

## Enterprise Web Development

### Development Environment
BLT can be operated on any "LAMP" stack that will run Drupal.

#### [DrupalVM](https://www.drupalvm.com/)
This project aims to make spinning up a simple local Drupal test/development environment incredibly quick and easy, and to introduce new developers to the wonderful world of Drupal development on local virtual machines (instead of crufty old MAMP/WAMP-based development).

#### [DevDesktop](https://www.acquia.com/products-services/dev-desktop)
Acquia Dev Desktop is a free app that allows you to run and develop Drupal sites locally on your computer and optionally host them using Acquia Cloud. Use Acquia Dev Desktop to evaluate Drupal, add and test other Drupal modules, and develop sites while on a plane or away from an internet connection.

**Recommended Reading**

* [Drupal VM Quickstart Guide](https://github.com/geerlingguy/drupal-vm#quick-start-guide)
* [BLT Onboarding](onboarding.md#initial-setup)

### Dependency Management

#### [Composer](https://getcomposer.org/)
Composer is a tool for dependency management in PHP. It allows you to declare the libraries your project depends on and it will manage (install/update) them for you.

**Recommended Reading**

 * [Getting Started with Composer](https://getcomposer.org/doc/00-intro.md)
 * [BLT Dependency Management](dependency-management.md)

**Common Commands**

 * [composer install](https://getcomposer.org/doc/03-cli.md#install)
 * [composer update](https://getcomposer.org/doc/03-cli.md#update)
 * [composer require](https://getcomposer.org/doc/03-cli.md#require)

## Version Control

### [Git](https://git-scm.com)
Git is a version control system (VCS) for tracking changes in computer files and coordinating work on those files among multiple people. It is primarily used for software development, but it can be used to keep track of changes in any files.

**Recommended Reading**

 * [Getting Started with Git](https://git-scm.com/book/en/v2/Getting-Started-About-Version-Control)
 * [BLT Repository Architecture](repo-architecture.md)
 * [BLT Git Workflow](dev-workflow.md#git-workflow)

**Common Commands**

  * [git add](https://git-scm.com/docs/git-add)
  * [git add -p](https://git-scm.com/docs/git-add#git-add--p)
  * [git checkout <branch>](https://git-scm.com/docs/git-checkout)
  * [git commit](https://git-scm.com/docs/git-commit)
  * [git commit --amend](https://git-scm.com/docs/git-commit#git-commit---amend)
  * [git push](https://git-scm.com/docs/git-push)
  * [git push -f](https://git-scm.com/docs/git-push#git-push--f)
  * [git rebase](https://git-scm.com/docs/git-rebase)
  * [git rebase -i](https://git-scm.com/docs/git-rebase#git-rebase---interactive)

## Deployment Management

### Continuous Integration / Deployment

#### [Travis CI](https://travis-ci.org/)
Travis CI is a hosted, distributed continuous integration service used to build and test software projects hosted at GitHub.

**Recommended Reading**

 * [BLT Travis CI](ci.md#travis-ci)

# Additional / Optional Skills

## GitHub
GitHub is a web-based Git repository hosting service. It offers all of the distributed version control and source code management (SCM) functionality of Git as well as adding its own features. It provides access control and several collaboration features such as bug tracking, feature requests, task management, and wikis for every project.

**Recommended Reading**

 * [Getting Started with GitHub](https://guides.github.com/activities/hello-world/)
 * [Git Forks](https://help.github.com/articles/fork-a-repo/)
 * [Understanding GitHub Flow](https://guides.github.com/introduction/flow/)
 * [Pull Requests](https://help.github.com/articles/about-pull-requests/)
 * [BLT GitHub Configuration](onboarding.md#github-configuration)

## [Automated Testing](testing.md)

### [Behat](http://behat.org)
Behat is an open source Behavior-Driven Development framework for PHP. It is a tool to support you in delivering software that matters through continous communication, deliberate discovery and test-automation.

**Recommended Reading**

* [Getting Started with Behat](http://behat.org/en/latest/quick_start.html)
* [Behat User Guide](http://behat.org/en/latest/user_guide.html)
* [BLT Automated Testing with Behat](testing.md#behat)
* [Behat Drupal Extension](https://www.drupal.org/project/drupalextension)
* [Behat Tags](http://behat.org/en/latest/user_guide/organizing.html)

### [PHPunit](https://phpunit.de)
PHPUnit is a programmer-oriented testing framework for PHP.

**Recommended Reading**

* [Getting Started with PHPUnit](https://phpunit.de/getting-started.html)
* [PHPUnit Documentat](https://phpunit.de/documentation.html)
* [BLT Automated Testing with PHPUnit](testing.md#phpunit)

## Back End Technologies

### [PHP](http://php.net)
PHP (recursive acronym for PHP: Hypertext Preprocessor) is a widely-used open source general-purpose scripting language that is especially suited for web development and can be embedded into HTML.

**Recommended Reading**

 * [Intro to PHP](http://php.net/manual/en/intro-whatis.php)
 * [Using xdebug to Debug PHP](https://xdebug.org/docs/)


We also strongly recommend an IDE for PHP Development Such as

 * [PHPStorm](https://www.jetbrains.com/phpstorm/)
 * [Netbeans](http://netbeans.org/features/php/)

## Front End Technologies
These are technologies used by the [COG theme](https://www.drupal.org/project/cog) and while not directly part of BLT, they are often integrated directly into BLT commands.

### [Gulp](http://gulpjs.com/)
gulp is a toolkit for automating painful or time-consuming tasks in your development workflow, so you can stop messing around and build something.

**Recommended Reading**

 * [Getting Started with Gulp](https://github.com/gulpjs/gulp/blob/master/docs/getting-started.md)

**Common Commands**

 * [gulp watch](https://github.com/gulpjs/gulp/blob/master/docs/API.md#gulpwatchglob--opts-tasks-or-gulpwatchglob--opts-cb)

### [SASS](http://sass-lang.com/)

**Recommended Reading**

 * [SASS Documentation](http://sass-lang.com/documentation/file.SASS_REFERENCE.html)
 * [BLT Front End Documentation](project-tasks.md#build-front-end-assets)

#### [NPM](https://www.npmjs.com/)
npm is the package manager for JavaScript. Find, share, and reuse packages of code from hundreds of thousands of developers — and assemble them in powerful new ways.

**Recommended Reading**

 * [Getting Started with NPM](https://docs.npmjs.com/)
 * [BLT Front End Dependencies](dependency-management.md#front-end-dependencies)

**Common Commands**

 * [npm install](https://docs.npmjs.com/getting-started/installing-npm-packages-locally)
 * npm run install-tools
 * npm run build
