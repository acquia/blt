# BLT installation

*Please do not clone BLT as a means of using it. The only reason to clone BLT is to contribute to it.

## System requirements

You must have the following tools on the command line of your *host operating system*:

* [Git](https://git-scm.com/)
* [Composer](https://getcomposer.org/download/)
* [PHP 5.6+](http://php.net/manual/en/install.php)

Instructions for installing _all_ requirements are listed below.

## Installing requirements

### Mac OSX

Ensure that [Xcode](https://itunes.apple.com/us/app/xcode/id497799835?mt=12) is installed. On OSX 10.9+ you can install Xcode with:

        sudo xcodebuild -license
        xcode-select --install

Then install the  minimum dependencies for BLT. The preferred method is via Homebrew, though you could install these yourself without a package manager.

        /usr/bin/ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)"
        brew tap homebrew/dupes; brew tap homebrew/versions; brew tap homebrew/homebrew-php;
        brew install php56 git composer drush
        composer global require "hirak/prestissimo:^0.3"

If you'd like to create a [Drupal VM](https://www.drupalvm.com/) with BLT, you will require the following additional libraries. If you'd like to use a LAMP stack other than Drupal VM, see [Local Development](readme/local-development.md).

        brew tap caskroom/cask
        brew cask install virtualbox vagrant
        vagrant plugin install vagrant-hostsupdater

The minimum required versions are VirtualBox 5.1.x and Vagrant 1.8.6.

The local PHP environment should also have a memory limit of at least 2G for BLT to initialize. You can modify your PHP CLI's memory limit by editing php.ini. You can use the following command to open the correct php.ini in TextEdit. Set `memory_limit = 2G`.

        open -a TextEdit $(php -i | grep "Loaded Configuration File" | cut -d" " -f 5)

If you'd like to execute Behat tests from the host machine, you will need Java:

        brew cask install java
        brew install chromedriver

BLT ships with the [Cog Base Theme](https://github.com/acquia-pso/cog) by default. Cog uses [npm](https://www.npmjs.com/) to install front end tools. If you intend to use Cog, you should also install the following tools:

        brew install npm nvm

### Windows

Windows is currently supported only when using the [Bash on Ubuntu on Windows](https://msdn.microsoft.com/en-us/commandline/wsl/about) feature available in the latest version of Windows 10.

Pre-requisite requirements:
  - You must be running a 64-bit version of Windows 10 Anniversary update (build 14393 or later)
  - Access to a local account with administrative rights for initial install

Follow the official [installation guide](https://msdn.microsoft.com/en-us/commandline/wsl/install_guide).

Note you **must** create a UNIX username with a password when prompted at the final step in the process. Certain BLT commands will not function correctly if you install with a passwordless root account.

Once complete follow the [BLT on Windows installation instructions](readme/windows-install.md).

### Linux

If you are using a Linux machine, it is assumed that you will not be using Drupal VM and that you will be configuring your own LAMP stack. Disregard the `blt vm` command and `@[project.machine_name]` references in subsequent documentation.

#### Ubuntu / Debian

        apt-get install git composer drush
        composer global require "hirak/prestissimo:^0.3"

#### Fedora

        dnf install git composer drush
        composer global require "hirak/prestissimo:^0.3"

# Installing BLT

Choose your own adventure:

* [Creating a new project with BLT](readme/creating-new-project.md)
* [Cloning an existing BLT project](readme/onboarding.md)
* [Adding BLT to an existing project](readme/adding-to-project.md)
* [Upgrading BLT](readme/updating-blt.md)
