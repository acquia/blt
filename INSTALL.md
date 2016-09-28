# BLT installation and updates

* [System requirements](#system-requirements)
* [Creating a new project with BLT](#creating-a-new-project-with-blt)
* [Adding BLT to an existing project](adding-blt.md)
* [Upgrading BLT](upgrading-blt.md)

## System requirements

You must have the following tools on the command line of your *host operating system*:

* [Git](https://git-scm.com/)
* [Composer](https://getcomposer.org/download/)
* [PHP 5.6+](http://php.net/manual/en/install.php)
    * PHP BZ2 extension is required (included by default in many cases).
        * Install with PECL `pecl install bz2`
        * Install with apt `apt-get install php5.6-bz2`

## Installing requirements

### Mac OSX

This will install all of the dependencies you need for BLT and DrupalVM. If you'd like to use a different \*AMP stack, see [Local Development](readme/local-development.md).

        /usr/bin/ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)"
        brew tap caskroom/cask
        brew install php56 git composer ansible drush
        brew cask install virtualbox vagrant
        composer global require "hirak/prestissimo:^0.3"

### Windows

See [Windows installation instructions]() for installing prerequisites. 

### Linux

If you are using a Linux machine, it is assumed that you will not be using Drupal VM and that you will be configuring your own LAMP stack. Disregard the `blt vm` command and `@[project.machine_name` references in subsequent documentation.

        apt-get install git composer drush
        composer global require "hirak/prestissimo:^0.3"

## Creating a new project with BLT

1. Create a new project using the [blt-project](https://github.com/acquia/blt-project) template:

        composer clear-cache
        export COMPOSER_PROCESS_TIMEOUT=2000
        composer create-project acquia/blt-project MY_PROJECT --no-interaction
        cd MY_PROJECT

1. Install the `blt` alias and follow on-screen instructions:

        composer blt-alias

1. Customize `project.yml`.
1. Create & boot the VM, install Drupal. 

        blt vm
        blt local:setup

1. Login to Drupal `drush @[project.machine_name].local uli`, where [project.machine_name] is the value that you set in project.yml.

## Next Steps

Now that your new project works locally, read through [README.md](https://github.com/acquia/blt/blob/8.x/template/README.md) (copied into new projects) to learn how to perform common project tasks and integrate with third party tools.

A few popular commands:

        # list targets
        blt
        
        # validate code via phpcs, php lint, composer validate, etc.
        blt validate
        
        # run phpunit tests
        blt tests:phpunit
        
        # ssh into vm & run behat tests
        drush @[project.machine_name].local ssh
        blt tests:behat
        
        # diagnose issues
        blt doctor
        
        # download & require a new project
        composer require drupal/ctools:^8.3.0
        
        # build a deployment artifact
        blt deploy:build
        
        # build artifact and deploy to git.remotes
        blt deploy
        
        # update BLT
        composer update acquia/blt --with-dependencies
