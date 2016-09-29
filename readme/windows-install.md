# Running BLT in Ubuntu on Bash on Windows

Once the installation steps for Ubuntu Bash have been completed you need to install all the necessary packages and configure for BLT.

To launch Bash on Windows, either run `bash` at a cmd/PowerShell command-prompt, or use the "Bash on Ubuntu on Windows" start menu shortcut.

## Required software installation

The following packages would apply to any fresh Ubuntu installation.

1. PHP

        sudo add-apt-repository ppa:ondrej/php
        sudo apt-get update
        sudo apt-get install php5.6 php5.6-curl php5.6-gd php5.6-mbstring php5.6-mcrypt php5.6-mysql php5.6-xml php5.6-xmlrpc php5.6-zip php5.6-bz2

2. Composer

        php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
        php composer-setup.php
        sudo mv composer.phar /usr/local/bin/composer

3. Node/npm

        sudo apt-get install nodejs npm
        sudo ln -s /usr/bin/nodejs /usr/local/bin/node

3. Various

        sudo apt-get install git unzip bzip2

## Usage notes

  - Local drives will be mounted in Linux at `/mnt/[drive-letter]`. You should setup your BLT codebase directly on a mounted local filesystem so your files are available to both Windows and Linux.
  - BLT sets up git hooks that occur on pre-commit. You should ensure you run git commands directly through bash (rather than Windows UI) to ensure you can view the output of these hooks correctly.
  - You may continue to use your preferred Windows IDE and WAMP stack of choice, and only need to run bash when issuing git or BLT commands.

## Next steps

Your environment is now ready to [create a new](../readme/creating-new-project.md) BLT project, or use an [existing one](../readme/onboarding.md).
