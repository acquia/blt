# BLT installation

## System requirements

You must have the following tools on the command line of your *host operating system*:

* [Git](https://git-scm.com/)
* [Composer](https://getcomposer.org/download/)
* [PHP 5.6+](http://php.net/manual/en/install.php)

## Installing requirements

### Mac OSX

Ensure that [Xcode](https://itunes.apple.com/us/app/xcode/id497799835?mt=12) is installed. On OSX 10.9+ you can install Xcode with:

        xcodebuild -license
        xcode-select --install

Then install the  minimum dependencies for BLT. The preferred method is via Homebrew, though you could install these yourself without a package manager.

        /usr/bin/ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)"
        brew install php56 git composer drush
        composer global require "hirak/prestissimo:^0.3"

If you'd like to create a VM with BLT, you will require the following additional libraries. If you'd like to use a LAMP stack other than Drupal VM, see [Local Development](readme/local-development.md).

        brew tap caskroom/cask
        brew install ansible
        brew cask install virtualbox vagrant

If you'd like to execute Behat tests from the host machine, you will need Java:

        brew cask install java
        brew install chromedriver

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
* [Adding BLT to an existing project](readme/adding-to-project.md)
* [Upgrading BLT](readme/updating-blt.md)
