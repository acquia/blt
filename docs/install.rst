.. include:: ../common/global.rst

Installing Acquia BLT
=====================

.. toctree::
   :hidden:
   :glob:

   /blt/install/windows-install/
   /blt/install/tooling/
   /blt/install/alt-env
   /blt/install/creating-new-project/
   /blt/install/adding-to-project/
   /blt/install/local-development/
   /blt/install/next-steps/
   /blt/install/updating-blt/

.. note::

   Do not clone the Acquia BLT repository to use it; only clone the
   repository to contribute to the Acquia BLT project.


.. _blt-system-requirements:

General requirements
--------------------

Regardless of the operating system you use, you must have the following
installed tools available for use from the command line:

-  `Git <https://git-scm.com/>`__
-  `Composer <https://getcomposer.org/download/>`__
-  `PHP 7.2+ minimum, 7.3+ recommended <http://php.net/manual/en/install.php>`__

Install the most recent version of these tools, unless otherwise noted.


.. _blt-networking:

Networking considerations
~~~~~~~~~~~~~~~~~~~~~~~~~

Building project dependencies requires your local computer to make HTTP and
HTTPS requests to several remote software providers. Ensure your local- and
network-level security settings permit these requests to occur.

If you must make requests using a proxy server, `configure Git to use a proxy
<http://stackoverflow.com/a/19213999>`__, which will address all Git-based
requests made by Composer.


.. _blt-installation-requirements:

Installing Acquia BLT
---------------------

After confirming your computer has the :ref:`required command line tools
<blt-system-requirements>` installed, use the following procedures
(based on your operating system) to both ensure you meet the Acquia BLT system
requirements, and to install Acquia BLT.

.. tabs::

   .. group-tab:: macOS

      To install Acquia BLT on macOS, complete the following steps:

      #.  Ensure that you have installed `Xcode
          <https://itunes.apple.com/us/app/xcode/id497799835?mt=12>`__. Xcode
          is required to support Homebrew, and you can install Xcode on macOS
          10.9 or greater by running the following commands:

          .. code-block:: bash

              sudo xcodebuild -license
              xcode-select --install

      #.  Install the minimum dependencies for Acquia BLT. Although you can
          use the following commands to use Homebrew to install the needed
          packages, you are not required to use a package manager:

          .. code-block:: bash

              /usr/bin/ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)"
              brew install php71 git composer drush
              composer global require "hirak/prestissimo:^0.3"

      #.  Install Drush **only** as a dependency of individual projects
          (instead of installing Drush system-wide). Acquia BLT will manage
          this dependency for you with projects, but for you to run Drush
          commands independently of Acquia BLT commands, `install the Drush
          Launcher
          <https://github.com/drush-ops/drush-launcher#installation---phar>`__.

      #.  Set up your LAMP stack for use with your development, based on your
          selected environment choice:

          *  `Drupal VM <https://www.drupalvm.com/>`__ – Use the installation
             instructions in the :ref:`Drupal VM requirements
             <blt-drupal-vm-blt-projects>`.

             .. note::

                When using Acquia BLT and Drupal VM on macOS 10.14 (Mojave),
                you must allow Full Disk Access for the iTerm application
                for files to be available in Drupal VM. `Learn more
                <https://github.com/geerlingguy/drupal-vm/issues/1828>`__.

          * Other LAMP stacks – See :doc:`/blt/install/local-development/`.

          .. note::

              If you are not using a virtual machine (VM) and you want to run
              Behat tests from the host computer, you will need to use Java.
              Use the following commands to install Java:

              .. code-block:: bash

                  brew cask install java
                  brew cask install chromedriver

      #.  If you want to use the `Cog base theme
          <https://github.com/acquia-pso/cog>`__ (included with Acquia BLT),
          run the following command to install additional tools:

          .. code-block:: bash

              brew install npm nvm

          .. note::

              Cog uses `npm <https://www.npmjs.com/>`__ to install front-end
              tools.

   .. group-tab:: Windows

      **Requirements**

      Acquia BLT on Windows has the following requirements:

      -  Running a 64-bit version of Windows 10 Anniversary update (build
         14393 or greater).
      -  Access to a local account with administrative rights for
         Acquia BLT's initial installation.
      - `Windows Subsystem for Linux
        <https://docs.microsoft.com/en-us/windows/wsl/about>`__
        (`installation instructions
        <https://docs.microsoft.com/en-us/windows/wsl/install-win10>`__)

        .. note::

              You must create a UNIX username with a password when prompted
              at the end of the installation process. Certain Acquia BLT
              commands will not function if you install the Windows Subsystem
              for Linux using an account without a password.

        If you cannot use WSL, you can instead set up virtualization, and
        then run Acquia BLT in a virtual machine (VM) or container running
        Windows, based on the following tools:

        -  `Docksal <https://blog.docksal.io/docksal-and-acquia-blt-1552540a3b9f>`__ –
           Supports VirtualBox and Docker
        -  `Lando <https://thinktandem.io/blog/2017/12/09/lando-blt-acquia/>`__ –
           Support Docker

      **Installation**

      To install the required applications for Acquia BLT (including PHP,
      Node.js, Git, and Composer), run the following commands:

      #.  Run the following command, and press Enter when prompted:

          .. code-block:: bash

              sudo add-apt-repository ppa:ondrej/php

      #.  Run the following command:

          .. code-block:: bash

              sudo apt-get update

      #.  Run the following command, based on your installed version of
          Acquia BLT:

          .. tabs::

             .. tab:: 10.x

                .. code-block:: bash

                   sudo apt-get install -y php7.2-cli php7.2-curl php7.2-xml php7.2-mbstring php7.2-bz2 php7.2-gd php7.2-mysql mysql-client unzip git

             .. tab:: 9.2.x

                .. code-block:: bash

                  sudo apt-get install -y php5.6-cli php5.6-curl php5.6-xml php5.6-mbstring php5.6-bz2 php5.6-gd php5.6-mysql mysql-client unzip git

      #.  Run the following command:

          .. code-block:: bash

              php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"

      #.  Run the following command:

          .. code-block:: bash

              php composer-setup.php

      #.  Run the following command:

          .. code-block:: bash

              sudo mv composer.phar /usr/local/bin/composer

      #.  Run the following command:

          .. code-block:: bash

              curl -sL https://deb.nodesource.com/setup_4.x | sudo -E bash -

      #.  Run the following command:

          .. code-block:: bash

              sudo apt-get install -y nodejs

      **Configuring Git**

      Before working with an Acquia BLT project, you must identify yourself
      to Git by running the following commands:

      .. code-block:: bash

          git config --global user.email "you@example.com"
          git config --global user.name "Your Name"

      If you haven't already configured an SSH identity (useful for working
      with projects on GitHub and interacting with your sites on
      Acquia Cloud), you should :doc:`generate an SSH key
      </acquia-cloud/manage/ssh/generate/>`.

   .. group-tab:: Linux

      Linux is fully supported by both Acquia BLT and Drupal VM, and shares
      many of the same dependencies as macOS (except for Xcode). Run the
      commands based on your installed version of Linux to install
      Acquia BLT:

      -  *Ubuntu or Debian* –

         .. code-block:: bash

              apt-get install git composer drush
              composer global require "hirak/prestissimo:^0.3"

      -  *Fedora* –

         .. code-block:: bash

              sudo dnf install -y git composer drush
              composer global require "hirak/prestissimo:^0.3"
              # To use NFS with Vagrant, nfs-utils package needs to be
              # installed and nfs-server needs to be running.
              # https://developer.fedoraproject.org/tools/vagrant/vagrant-nfs.html
              sudo dnf install -y nfs-utils && sudo systemctl enable nfs-server
              # Enable nfs, rpc-bind and mountd services for firewalld
              sudo firewall-cmd --permanent --add-service=nfs \
                  && sudo firewall-cmd --permanent --add-service=rpc-bind \
                  && sudo firewall-cmd --permanent --add-service=mountd \
                  && sudo firewall-cmd --reload

Getting started with Acquia BLT
-------------------------------

After you have successfully installed Acquia BLT, select from the following
resources to assist you with your development:

-  :doc:`/blt/install/creating-new-project/`
-  :doc:`/blt/developer/onboarding/`
-  :doc:`/blt/install/adding-to-project/`
-  :doc:`/blt/install/updating-blt/`

.. Next review date 20200422
