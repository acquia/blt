# BLT installation

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

Ensure that [Xcode](https://itunes.apple.com/us/app/xcode/id497799835?mt=12) is installed. On OSX 10.9+ you can install Xcode with:

        xcodebuild -license
        xcode-select --install

This will install all remaining dependencies for BLT and DrupalVM. If you'd like to use a LAMP stack other than Drupal VM, see [Local Development](readme/local-development.md).

        /usr/bin/ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)"
        brew tap caskroom/cask
        brew install php56 git composer ansible drush
        brew cask install virtualbox vagrant
        composer global require "hirak/prestissimo:^0.3"

### Windows

Windows is currently unsupported.

### Linux

If you are using a Linux machine, it is assumed that you will not be using Drupal VM and that you will be configuring your own LAMP stack. Disregard the `blt vm` command and `@[project.machine_name]` references in subsequent documentation.

        apt-get install git composer drush
        composer global require "hirak/prestissimo:^0.3"

# Installing BLT

Choose your own adventure:

* [Creating a new project with BLT](creating-new-project.md)
* [Adding BLT to an existing project](adding-to-project.md)
* [Upgrading BLT](upgrading-blt.md)
